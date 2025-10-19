<?php

namespace BitApps\Social\HTTP\Services\Social\FacebookService;

use AllowDynamicProperties;
use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\HTTP\Services\Interfaces\SocialInterface;
use BitApps\Social\HTTP\Services\Traits\LoggerTrait;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;

#[AllowDynamicProperties]
class PostPublishFacebookService implements SocialInterface
{
    use Common, LoggerTrait;

    private $httpHandler;

    /**
     * @var RefreshService $refreshHandler
     */
    private $refreshHandler;

    private $baseUrl = 'https://graph.facebook.com/';

    private $postBaseUrl = 'https://fb.com';

    private $version = 'v19.0';

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
        $this->refreshHandler = new RefreshFacebookService();
    }
    // publish post in page and group

    public function publishPost($data)
    {
        $post_data = [];
        $post = $data['post'] ?? null;
        $scheduleType = $data['schedule_type'] ?? null;
        $template = (object) $data['template'];
        $account_detail = $data['account_details'];
        $schedule_id = $data['schedule_id'] ?? null;
        $account_id = $account_detail->account_id;
        $account_name = $account_detail->account_name;
        $accessToken = $account_detail->access_token;
        $generates_on = $account_detail->generates_on;

        $tokenUpdateData = $this->refreshHandler->tokenExpiryCheck($accessToken, $generates_on);
        if ($tokenUpdateData->access_token !== $accessToken) {
            $account_detail->access_token = $tokenUpdateData->access_token;
            $account_detail->generates_on = $tokenUpdateData->generates_on;
            $this->refreshHandler->saveRefreshedToken($account_detail);
        }

        if ($scheduleType === Schedule::scheduleType['DIRECT_SHARE']) {
            $templateMedia = array_map(function ($item) {
                return $item['url'];
            }, $template->media);

            $post_data['content'] = $template->content ?? null;
            $post_data['images'] = $templateMedia ?? null;
            $post_data['link'] = $template->link ?? null;

            $template->isFeaturedImage = false;
            $template->isLinkCard = false;

            if (!empty($templateMedia)) {
                $template->isFeaturedImage = true;
            }
            if (empty($templateMedia) && !empty($template->link)) {
                $template->isLinkCard = true;
            }
        } else {
            $template->platform = 'facebook';
            $post_data = $this->replacePostContent($post['ID'], $template);
        }

        $result = $this->facebookPostPublish($post_data, $scheduleType, $account_id, $tokenUpdateData->access_token);

        $publishPostResponse = $result['response'];
        $publishPostImageError = $result['imageErrors'];

        $responseData = [
            'schedule_id' => $schedule_id,
            'details'     => [
                'account_id'   => $account_id,
                'account_name' => $account_name,
                'response'     => $publishPostResponse,
                'post_id'      => $post['ID'] ?? null,
                'post_url'     => isset($publishPostResponse->id) ? "{$this->postBaseUrl}/{$publishPostResponse->id}" : ''
            ],
            'platform' => 'facebook',
        ];

        if ($publishPostImageError) {
            $responseData['details']['imagePostError'] = $publishPostImageError;
        }

        if (isset($publishPostResponse->id)) {
            $responseData['status'] = 1;
        } else {
            $responseData['status'] = 0;
        }

        if (\array_key_exists('retry', $data) && $data['retry'] === true) {
            $this->logUpdate($responseData, $data['log_id']);

            return;
        }
        if ($responseData['status'] === 1) {
            $this->logCreate($responseData);

            return;
        }

        $this->logCreate($responseData);
    }

    public function publishPostGroup($post_data, $scheduleType, $accountId, $accessToken, $featuredImage)
    {
        $end_point = 'feed';
        $featuredUrlMapped = !empty($post_data['featureImage']);
        $imageUrl = $featuredUrlMapped ? $post_data['featureImage'][0] : '';
        $params = [
            'published'    => 'true',
            'access_token' => $accessToken,
            'message'      => $post_data['content'],
        ];

        // photo must be available in the server
        if ($featuredImage) {
            $params['link'] = $imageUrl;
        }
        if ($featuredUrlMapped) {
            $params['message'] .= ' ' . $imageUrl;
        }
        if ($featuredImage) {
            $end_point = 'photos';
            $params['url'] = $imageUrl;
            $params['caption'] = $params['message'];

            unset($params['message']);
        }

        $publishInFeedUrl = $this->baseUrl . "{$this->version}/{$accountId}/{$end_point}?";

        $publishInFeedUrlWithParams = $publishInFeedUrl . http_build_query($params);

        return $this->httpHandler->request($publishInFeedUrlWithParams, 'POST', []);
    }

    public function facebookPostPublish($post_data, $scheduleType, $account_id, $accessToken)
    {
        $featuredImage = $post_data['featureImage'] ?? null;
        $linkedCard = $post_data['link'];
        $allImages = $post_data['allImages'] ?? null;
        $imageErrors = [];

        if ($scheduleType === Schedule::scheduleType['DIRECT_SHARE']) {
            $featuredImage = $post_data['images'];
        }

        $end_point = 'feed';
        $params = [
            'published'    => 'true',
            'access_token' => $accessToken,
            'message'      => $post_data['content']
        ];

        // photo must be available or live on internet not in local machine
        if ($featuredImage) {
            $mediaResponse = $this->mediaUpload($featuredImage, $account_id, $accessToken);
            $params['attached_media'] = $mediaResponse['imageIds'];
            $imageErrors = $mediaResponse['errors'];
        }

        if ($allImages) {
            $mediaResponse = $this->mediaUpload($allImages, $account_id, $accessToken);
            $params['attached_media'] = $mediaResponse['imageIds'];
            $imageErrors = $mediaResponse['errors'];
        }

        if ($linkedCard) {
            $params['link'] = $linkedCard;
        }

        $publishPostUrl = $this->baseUrl . "/{$this->version}/{$account_id}/{$end_point}?";
        $publishPostUrlWithParams = $publishPostUrl . http_build_query($params);
        $response = $this->httpHandler->request($publishPostUrlWithParams, 'POST', []);

        return ['response' => $response, 'imageErrors' => $imageErrors];
    }

    public function mediaUpload($imageLinks, $accountId, $accessToken)
    {
        $imageIds = [];
        $errors = [];

        if (\is_array($imageLinks)) {
            foreach ($imageLinks as $imageLink) {
                $response = $this->facebookMediaUpload($imageLink, $accountId, $accessToken);

                if (isset($response->id)) {
                    $mediaId['media_fbid'] = $response->id;
                    $imageIds[] = $mediaId;
                } else {
                    $errors[] = ['image' => $imageLink, 'message' => $response->error];
                }
            }
        }

        if (\is_string($imageLinks)) {
            $response = $this->facebookMediaUpload($imageLinks, $accountId, $accessToken);
            if (isset($response->id)) {
                $mediaId['media_fbid'] = $response->id;
                $imageIds[] = $mediaId;
            } else {
                $errors[] = ['image' => $imageLinks, 'message' => $response->error];
            }
        }

        return ['imageIds' => $imageIds, 'errors' => $errors];
    }

    public function facebookMediaUpload($imageLink, $accountId, $accessToken)
    {
        $endPoint = 'photos';
        $params['url'] = $imageLink;
        $params['access_token'] = $accessToken;
        $params['published'] = 'false';
        $publishPostUrl = $this->baseUrl . "{$accountId}/{$endPoint}?";
        $publishPostUrlWithParams = $publishPostUrl . http_build_query($params);

        return $this->httpHandler->request($publishPostUrlWithParams, 'POST', []);
    }
}
