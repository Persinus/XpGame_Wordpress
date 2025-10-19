<?php

namespace BitApps\Social\HTTP\Services\Social\FacebookService;

use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\HTTP\Services\Traits\LoggerTrait;
use BitApps\Social\Model\Account;
use BitApps\Social\Utils\Common;
use stdClass;

class RefreshFacebookService
{
    use Common, LoggerTrait;

    private $httpHandler;

    private $baseUrl = 'https://graph.facebook.com/';

    private $baseServerUrl = 'https://auth-apps.bitapps.pro/apps/';

    private $version = 'v16.0';

    private $facebookAppIdUrl;

    private $facebookAppSecretUrl;

    private $refreshTokenUrl;

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
        $this->facebookAppIdUrl = $this->baseServerUrl . 'facebook';
        $this->facebookAppSecretUrl = $this->baseServerUrl . 'secret';
        $this->refreshTokenUrl = $this->baseUrl . $this->version . '/oauth/access_token?';
    }

    public function tokenExpiryCheck($accessToken, $generates_on)
    {
        if (!$accessToken && !$generates_on) {
            return false;
        }
        // token expires after 50 days
        $TIME_50_DAYS = 50 * 24 * 60 * 60;
        if ((\intval($generates_on) + $TIME_50_DAYS) < time()) {
            $refreshToken = $this->refreshToken($accessToken);
            if (\is_object($refreshToken) && !property_exists($refreshToken, 'access_token')) {
                return false;
            }
            $data = new stdClass();
            $data->access_token = $refreshToken->access_token;
            $data->generates_on = $refreshToken->generates_on;

            return $data;
        }
        $data = new stdClass();
        $data->access_token = $accessToken;
        $data->generates_on = $generates_on;

        return $data;
    }

    public function refreshToken($accessToken)
    {
        $facebookAppIdData = $this->httpHandler->request($this->facebookAppIdUrl, 'GET', []);
        $facebookAppId = $facebookAppIdData->clientId;
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $getSecretParams = [
            'client_id' => $facebookAppId,
            'platform'  => 'facebook',
        ];

        $facebookAppSecret = $this->httpHandler->request($this->facebookAppSecretUrl, 'POST', json_encode($getSecretParams), $headers);
        if (empty($facebookAppSecret->clientSecret)) {
            return false;
        }
        $facebookAppSecret = $facebookAppSecret->clientSecret;
        $longTimeAccessTokenParams = [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => $facebookAppId,
            'client_secret'     => $facebookAppSecret,
            'fb_exchange_token' => $accessToken,
        ];

        $refreshTokenUrlWithParams = $this->refreshTokenUrl . http_build_query($longTimeAccessTokenParams);
        $apiResponse = $this->httpHandler->request($refreshTokenUrlWithParams, 'GET', []);
        if (!property_exists($apiResponse, 'access_token')) {
            return false;
        }
        $data = new stdClass();
        $data->access_token = $apiResponse->access_token;
        $data->generates_on = time();

        return $data;
    }

    public function saveRefreshedToken($account_detail)
    {
        $accountId = $account_detail->account_id;
        if (empty($accountId)) {
            return;
        }
        $account = Account::findOne(['account_id' => $accountId]);
        $account->update(['details' => $account_detail]);
    }
}
