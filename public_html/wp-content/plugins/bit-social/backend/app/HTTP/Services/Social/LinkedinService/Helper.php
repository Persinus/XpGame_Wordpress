<?php

namespace BitApps\Social\HTTP\Services\Social\LinkedinService;

use BitApps\Social\Utils\Hash;

class Helper
{
    public const LINKEDIN_VERSION = 20240101;

    public static function makeHeader($accessToken)
    {
        return [
            'Authorization'             => "Bearer {$accessToken}",
            'X-RestLi-Protocol-Version' => '2.0.0',
            'Connection'                => 'Keep-Alive',
            'X-li-format'               => 'json',
            'Content-Type'              => 'application/json',
        ];
    }

    // this header used when post publish on profile and page
    public static function publishHeader($access_token)
    {
        return [
            'Authorization'             => "Bearer {$access_token}",
            'X-Restli-Protocol-Version' => '2.0.0',
            'LinkedIn-Version'          => static::LINKEDIN_VERSION,
            'Content-Type'              => 'application/json',
            'Connection'                => 'Keep-Alive',
        ];
    }

    public static function organizeToken($client_id, $client_secret, $tokenInfo)
    {
        return [
            'client_id'                => Hash::encrypt($client_id),
            'client_secret'            => Hash::encrypt($client_secret),
            'access_token'             => Hash::encrypt($tokenInfo->access_token),
            'expires_in'               => time() + $tokenInfo->expires_in,
            'refresh_token'            => Hash::encrypt($tokenInfo->refresh_token),
            'refresh_token_expires_in' => time() + $tokenInfo->refresh_token_expires_in,
        ];
    }

    public static function initializeHeader($access_token)
    {
        return [
            'Authorization'             => "Bearer {$access_token}",
            'LinkedIn-Version'          => static::LINKEDIN_VERSION,
            'X-RestLi-Protocol-Version' => '2.0.0',
            'Content-Type'              => 'application/json'
        ];
    }

    public static function organizeCompanies($companiesData)
    {
        foreach ($companiesData['elements'] as $company) {
            $logo = !empty($company['organizationalTarget~']['logoV2']) ? $company['organizationalTarget~']['logoV2']['original~']['elements'][0]['identifiers'][0]['identifier'] : '';
            $companies[] = [
                'account_type' => 'page',
                'urn'          => $company['organizationalTarget'],
                'account_id'   => $company['organizationalTarget~']['id'],
                'account_name' => $company['organizationalTarget~']['localizedName'],
                'icon'         => $logo,
            ];
        }

        return $companies;
    }

    public static function imageUpload($initializeImageUrl, $params, $initializeHeader, $feature_image, $allImages, $access_token, $httpHandler)
    {
        if (!empty($allImages) && \is_array($allImages)) {
            foreach ($allImages as $image) {
                $response = $httpHandler->request($initializeImageUrl, 'POST', $params, $initializeHeader);

                $upload_url = $response->value->uploadUrl;
                $imageUrn = $response->value->image;

                $parameters = self::getLocalImagePath($image);

                $uploadHeader = self::uploadHeader($access_token, $parameters);

                $imageUploadResponse = $httpHandler->request($upload_url, 'PUT', $parameters, $uploadHeader);

                $allImageUrns[] = $imageUrn;
            }

            return $allImageUrns;
        } elseif (!empty($feature_image)) {
            $response = $httpHandler->request($initializeImageUrl, 'POST', $params, $initializeHeader);

            $upload_url = $response->value->uploadUrl;
            $imageUrn = $response->value->image;

            $parameters = self::getLocalImagePath($feature_image);

            $uploadHeader = self::uploadHeader($access_token, $parameters);

            $httpHandler->request($upload_url, 'PUT', $parameters, $uploadHeader);

            return $imageUrn;
        }
    }

    public static function getLocalImagePath($image)
    {
        $localPath = wp_upload_dir();
        $localBaseUrl = $localPath['baseurl'];
        $localBaseDir = $localPath['basedir'];
        $localImagePath = str_replace($localBaseUrl, $localBaseDir, $image);

        return file_get_contents($localImagePath);
    }

    public static function uploadHeader($access_token, $parameters)
    {
        return [
            'Authorization'             => "Bearer {$access_token}",
            'LinkedIn-Version'          => static::LINKEDIN_VERSION,
            'X-RestLi-Protocol-Version' => '2.0.0',
            'Content-Type'              => 'image/jpeg',
            'Content-Length'            => \strlen($parameters),
        ];
    }

    public static function makeImageContent($imageUrn)
    {
        if (\is_array($imageUrn)) {
            if (\count($imageUrn) === 1) {
                return [
                    'media' => [
                        'id'      => $imageUrn[0],
                        'title'   => '',
                        'altText' => '',
                    ]
                ];
            }
            foreach ($imageUrn as $urn) {
                $images[] = [
                    'id'      => $urn,
                    'altText' => ''
                ];
            }

            return [
                'multiImage' => [
                    'images' => $images
                ]
            ];
        }

        return [
            'media' => [
                'id'      => $imageUrn,
                'title'   => '',
                'altText' => '',
            ]
        ];
    }

    public static function commonParams($ownerUrn, $post_content)
    {
        return [
            'author'       => $ownerUrn,
            'commentary'   => $post_content,
            'visibility'   => 'PUBLIC',
            'distribution' => [
                'feedDistribution'               => 'MAIN_FEED',
                'targetEntities'                 => [],
                'thirdPartyDistributionChannels' => []
            ],
            'lifecycleState'            => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false
        ];
    }

    public static function getContents($url, $method = 'GET', $data = [], $headers = [], $proxy = '', $postDataHBQ = false, $sendUserAgent = true)
    {
        $method = strtoupper($method);

        $c = curl_init();

        $user_agents = [
            'Mozilla/5.0 (Linux; Android 5.0.2; Andromax C46B2G Build/LRX22G) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/37.0.0.0 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/60.0.0.16.76;]'
        ];

        $useragent = $user_agents[array_rand($user_agents)];

        if ($method === 'GET' && ! empty($data) && \is_array($data)) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . http_build_query($data);
        }

        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 2
        ];

        if ($sendUserAgent) {
            $opts[CURLOPT_USERAGENT] = $useragent;
        }

        if (! empty($proxy)) {
            $opts[CURLOPT_PROXY] = $proxy;
        }

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $postDataHBQ ? http_build_query($data) : $data;
        } else {
            if ($method === 'DELETE') {
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opts[CURLOPT_POST] = true;
                $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
        }

        if (\is_array($headers) && ! empty($headers)) {
            $headers_arr = [];
            foreach ($headers as $k => $v) {
                $headers_arr[] = $k . ': ' . $v;
            }

            $opts[CURLOPT_HTTPHEADER] = $headers_arr;
        }

        curl_setopt_array($c, $opts);

        $result = curl_exec($c);

        $cError = curl_error($c);

        if ($cError) {
            return json_encode([
                'error' => [
                    'message' => htmlspecialchars($cError)
                ]
            ]);
        }

        curl_close($c);

        unset($c);

        return $result;
    }

    public static function getFileType($file)
    {
        $attachmentPostId = attachment_url_to_postid($file);
        $info = wp_get_attachment_metadata($attachmentPostId);

        if (isset($info['mime_type'])) {
            return $info['mime_type'];
        } elseif (isset($info['sizes'], $info['sizes']['medium']) && $info['sizes']['medium']['mime-type']) {
            return $info['sizes']['medium']['mime-type'];
        }
    }
}
