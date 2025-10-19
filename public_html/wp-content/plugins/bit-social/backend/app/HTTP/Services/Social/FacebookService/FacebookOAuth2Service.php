<?php

namespace BitApps\Social\HTTP\Services\Social\FacebookService;

use BitApps\Social\Deps\BitApps\WPKit\Helpers\Arr;
use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Services\Interfaces\AuthServiceInterface;
use BitApps\Social\HTTP\Services\Traits\LoggerTrait;
use BitApps\Social\Model\Account;
use BitApps\Social\Utils\Common;

class FacebookOAuth2Service implements AuthServiceInterface
{
    use Common, LoggerTrait;

    private $httpHandler;

    private $baseUrl = 'https://graph.facebook.com/';

    private $version = 'v16.0';

    // all urls
    private $shortAccessTokenUrl;

    private $longTimeAccessTokenUrl;

    private $userAccountInfoUrl;

    private $getAccountsUrl;

    private $getAllGroupUrl;

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
        $this->shortAccessTokenUrl = $this->baseUrl . $this->version . '/oauth/access_token?';
        $this->longTimeAccessTokenUrl = $this->baseUrl . $this->version . '/oauth/access_token?';
        $this->userAccountInfoUrl = $this->baseUrl . 'me?';
        $this->getAccountsUrl = $this->baseUrl . 'me/accounts?';
        $this->getAllGroupUrl = $this->baseUrl . 'me/groups?';
    }

    // TODO: encode only query params value. should  refactor
    public function urlParamEncode($url)
    {
        // Parse the URL to get the query string
        $queryString = parse_url($url, PHP_URL_QUERY);

        // Separate the query string into key-value pairs
        parse_str($queryString, $params);

        if (isset($params['rest_route'])) {
            // Encode each value in the parameters array
            foreach ($params as $key => $value) {
                $params[$key] = urlencode($value);
            }

            // Rebuild the query string

            $newQueryString = 'rest_route=' . $params['rest_route'];

            // Reconstruct the URL with the encoded query string
            return str_replace($queryString, $newQueryString, $url);
        }

        return $url;
    }

    // TODO: handle response properly. Follow pattern from twitter service.
    public function authHandler($request)
    {
        $body = $request->body();
        $app_id = $body['payload']['client_id'];
        $client_secret = $body['payload']['client_secret'];
        $redirect_uri = $body['payload']['redirect_uri'];
        $code = $body['payload']['code'];

        $shortAccessTokenParams = [
            'client_id'     => $app_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $this->urlParamEncode($redirect_uri),
            'code'          => $code,
        ];

        $shortAccessTokenUrlWithParams = $this->shortAccessTokenUrl . http_build_query($shortAccessTokenParams);

        $shortAccessTokenResponse = $this->httpHandler->request($shortAccessTokenUrlWithParams, 'GET', []);

        $longTimeAccessTokenParams = [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $app_id,
            'client_secret'     => $client_secret,
            'fb_exchange_token' => $shortAccessTokenResponse->access_token,
            'redirect_uri'      => $this->urlParamEncode($redirect_uri),
        ];

        $longTimeAccessTokenUrlWithParams = $this->longTimeAccessTokenUrl . http_build_query($longTimeAccessTokenParams);

        $longTimeAccessTokenData = $this->httpHandler->request($longTimeAccessTokenUrlWithParams, 'GET', []);
        $longTimeAccessToken = $longTimeAccessTokenData->access_token;
        $appsecret_proof = hash_hmac(
            'sha256',
            $longTimeAccessToken,
            $client_secret
        );

        $userInfoData = self::getUserInfo($longTimeAccessToken, $appsecret_proof);
        $accountsPages = $this->accountsPages($longTimeAccessToken, $appsecret_proof);
        $accountsGroups = $this->accountsGroups($longTimeAccessToken, $appsecret_proof);

        $pageAndGroup = $this->organizePageAndGroup($accountsPages->data, $accountsGroups->data, $userInfoData, $longTimeAccessToken);

        if (\is_array($pageAndGroup)) {
            $allAccount = [];
            $arr = new Arr();

            $accountIds = $arr->pluck($pageAndGroup, 'account_id');
            $existAccountIds = Account::whereIn('account_id', $accountIds)->get('account_id');

            if (!\is_array($existAccountIds)) {
                $existAccountIds = [];
            }

            $existAccountIds = $arr->pluck($existAccountIds, 'account_id');

            foreach ($pageAndGroup as $item) {
                $isConnected = \in_array($item['account_id'], $existAccountIds);

                $data['profile_id'] = $userInfoData->id;
                $data['account_id'] = $item['account_id'];
                $data['account_name'] = $item['account_name'];
                $data['details'] = json_encode($item);
                $data['platform'] = 'facebook';
                $data['account_type'] = Account::accountType['DEFAULT'];
                $data['status'] = Account::ACCOUNT_STATUS['active'];
                $allAccount[] = ['account' => $data, 'isConnected' => $isConnected];
            }
        }
        if (empty($allAccount)) {
            return Response::error('Something went wrong');
        }

        return $allAccount;
    }

    public function getUserInfo($longTimeAccessToken, $appsecret_proof)
    {
        $userInfoQuery = [
            'fields'          => 'id,name,email',
            'access_token'    => $longTimeAccessToken,
            'appsecret_proof' => $appsecret_proof
        ];

        $userAccountInfoUrlWithParams = $this->userAccountInfoUrl . http_build_query($userInfoQuery);

        return $this->httpHandler->request($userAccountInfoUrlWithParams, 'GET', []);
    }

    public function accountsPages($longTimeAccessToken, $appsecret_proof)
    {
        $accountQuery = [
            'fields'          => 'access_token,category,name,id',
            'limit'           => '100',
            'access_token'    => $longTimeAccessToken,
            'appsecret_proof' => $appsecret_proof
        ];

        $getAccountsUrlWithParams = $this->getAccountsUrl . http_build_query($accountQuery);

        return $this->httpHandler->request($getAccountsUrlWithParams, 'GET', []);
    }

    public function accountsGroups($longTimeAccessToken, $appsecret_proof)
    {
        $accountQuery = [
            'fields'          => 'name,privacy,id,icon,cover{source},administrator',
            'limit'           => '100',
            'access_token'    => $longTimeAccessToken,
            'appsecret_proof' => $appsecret_proof
        ];

        $getAllGroupUrlWithParams = $this->getAllGroupUrl . http_build_query($accountQuery);

        return $this->httpHandler->request($getAllGroupUrlWithParams, 'GET', []);
    }

    public function fetchPageProfilePic($accessToken, $accountId)
    {
        $params = [
            'fields'       => 'picture',
            'access_token' => $accessToken
        ];

        $pagePicUrl = $this->baseUrl . "{$accountId}?";
        $pagePicUrlWithParams = $pagePicUrl . http_build_query($params);

        $response = $this->httpHandler->request($pagePicUrlWithParams, 'GET', []);

        return $response->picture->data->url;
    }

    public function organizePageAndGroup($accountsPages, $accountsGroups, $userInfoData, $longTimeAccessToken)
    {
        foreach ($accountsPages as $page) {
            $pages[] = [
                'user_name'    => $userInfoData->name,
                'account_id'   => $page->id,
                'account_name' => $page->name,
                'account_type' => 'page',
                'category'     => $page->category,
                'icon'         => $this->fetchPageProfilePic($longTimeAccessToken, $page->id),
                'access_token' => $page->access_token,
                'generates_on' => time()
            ];
        }

        foreach ($accountsGroups as $group) {
            $groups[] = [
                'user_name'     => $userInfoData->name,
                'account_id'    => $group->id,
                'account_name'  => $group->name,
                'privacy'       => $group->privacy,
                'account_type'  => 'group',
                'icon'          => $group->icon,
                'administrator' => $group->administrator,
                'access_token'  => $longTimeAccessToken,
                'generates_on'  => time()
            ];
        }

        if (!empty($pages) && !empty($groups)) {
            $pageAndGroup = array_merge($pages, $groups);
        } elseif (!empty($pages)) {
            $pageAndGroup = $pages;
        } elseif (!empty($groups)) {
            $pageAndGroup = $groups;
        }

        return $pageAndGroup;
    }
}
