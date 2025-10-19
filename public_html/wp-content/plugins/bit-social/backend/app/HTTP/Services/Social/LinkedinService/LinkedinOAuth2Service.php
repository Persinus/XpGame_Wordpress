<?php

namespace BitApps\Social\HTTP\Services\Social\LinkedinService;

use BitApps\Social\Deps\BitApps\WPKit\Helpers\Arr;
use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Services\Traits\LoggerTrait;
use BitApps\Social\Model\Account;
use BitApps\Social\Utils\Common;
use BitApps\SocialPro\Config as ProConfig;

class LinkedinOAuth2Service
{
    use Common, LoggerTrait;

    private $httpHandler;

    private $baseUrl = 'https://www.linkedin.com/';

    private $apiBaseUrl = 'https://api.linkedin.com/';

    private $version = 'v2/';

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
    }

    public function authHandler($request)
    {
        $body = $request->body();
        $client_id = $body['payload']['client_id'];
        $client_secret = $body['payload']['client_secret'];
        $redirect_uri = $body['payload']['redirect_uri'];
        $code = $body['payload']['code'];

        $tokenInfo = $this->getAccessToken($client_id, $client_secret, $redirect_uri, $code);

        if (property_exists($tokenInfo, 'error') && $tokenInfo->error) {
            return Response::error($tokenInfo->error);
        }

        if (!\is_object($tokenInfo) && !isset($tokenInfo->access_token)) {
            return (object) ['status' => 'error', 'message' => 'Access token is not valid, please authorize again!'];
        }
        $access_token = $tokenInfo->access_token;

        $userAccount = $this->getUserAccount($access_token);

        $tokenDetail = Helper::organizeToken($client_id, $client_secret, $tokenInfo);

        $companies = null;

        if (class_exists(ProConfig::class) && is_plugin_active(ProConfig::get('BASENAME'))) {
            $companies = apply_filters(ProConfig::VAR_PREFIX . 'get_linkedin_company_page', $this->apiBaseUrl, $this->version, $access_token);
        }

        return $this->accountOrganize($userAccount, $tokenDetail, $companies);
    }

    public function getAccessToken($client_id, $client_secret, $redirect_uri, $code)
    {
        $accessTokenUrl = $this->baseUrl . 'oauth/' . $this->version . 'accessToken';

        $header = ['Content-Type' => 'application/x-www-form-urlencoded'];
        $params = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri'  => $redirect_uri
        ];

        return $this->httpHandler->request($accessTokenUrl, 'POST', $params, $header);
    }

    public function getUserAccount($accessToken)
    {
        $header = [
            'X-RestLi-Protocol-Version' => '2.0.0',
            'Connection'                => 'Keep-Alive',
            'X-li-format'               => 'json',
            'Content-Type'              => 'application/x-www-form-urlencoded',
        ];

        $user_profile_url = 'https://api.linkedin.com/v2/userinfo?oauth2_access_token=' . $accessToken;
        $person = $this->httpHandler->request($user_profile_url, 'GET', null, $header);

        return [
            'account_type' => 'profile',
            'urn'          => "urn:li:person:{$person->sub}",
            'account_id'   => $person->name,
            'account_name' => $person->name,
            'icon'         => isset($person->picture) ? $person->picture : '',
        ];
    }

    public function accountOrganize($userAccount, $tokenDetail, $companies)
    {
        $userAccount = array_merge($userAccount, $tokenDetail);

        if (!empty($companies)) {
            foreach ($companies as $company) {
                $companiesData[] = array_merge($company, $tokenDetail);
            }
            $allAccounts = array_merge([$userAccount], $companiesData);
        } else {
            $allAccounts = [$userAccount];
        }

        $arr = new Arr();
        $accountIds = $arr->pluck($allAccounts, 'account_id');

        $existAccountIds = Account::whereIn('account_id', $accountIds)->get('account_id');

        if (!\is_array($existAccountIds)) {
            $existAccountIds = [];
        }

        $existAccountIds = $arr->pluck($existAccountIds, 'account_id');

        if (\is_array($allAccounts)) {
            foreach ($allAccounts as $account) {
                $isConnected = \in_array($account['account_id'], $existAccountIds);

                $data['profile_id'] = $account['account_id'];
                $data['account_id'] = $account['account_id'];
                $data['account_name'] = $account['account_name'];
                $data['details'] = json_encode($account);
                $data['platform'] = 'linkedin';
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
}
