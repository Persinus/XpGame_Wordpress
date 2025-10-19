<?php

namespace BitApps\Social\HTTP\Services\Social\LinkedinService;

use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\HTTP\Services\Traits\LoggerTrait;
use BitApps\Social\Model\Account;
use BitApps\Social\Utils\Common;
use BitApps\Social\Utils\Hash;

class LinkedinRefreshTokenService
{
    use Common, LoggerTrait;

    private $httpHandler;

    private $baseUrl = 'https://www.linkedin.com/oauth/';

    private $version = 'v2';

    private $refreshTokenUrl;

    private $accountId;

    public function __construct()
    {
        $this->httpHandler = new HttpClient();
        $this->refreshTokenUrl = $this->baseUrl . $this->version . '/accessToken';
    }

    public function tokenExpiryCheck($client_id, $client_secret, $refresh_token, $account_id)
    {
        $this->accountId = $account_id;

        return $this->refreshAccessToken($client_id, $client_secret, $refresh_token);
    }

    public function refreshAccessToken($client_id, $client_secret, $refresh_token)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $params = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
        ];

        $tokenResponse = $this->httpHandler->request($this->refreshTokenUrl, 'POST', $params, $headers);

        $this->saveRefreshedToken($tokenResponse);

        return $tokenResponse->access_token;
    }

    public function saveRefreshedToken($tokenResponse)
    {
        if (empty($this->accountId)) {
            return;
        }
        $account = Account::findOne(['account_id' => $this->accountId]);
        $details = $account->details;
        $details->access_token = Hash::encrypt($tokenResponse->access_token);
        $details->expires_in = time() + $tokenResponse->expires_in;
        $details->refresh_token = Hash::encrypt($tokenResponse->refresh_token);
        $details->refresh_token_expires_in = time() + $tokenResponse->refresh_token_expires_in;
        $account->update(['details' => $details])->save();
    }
}
