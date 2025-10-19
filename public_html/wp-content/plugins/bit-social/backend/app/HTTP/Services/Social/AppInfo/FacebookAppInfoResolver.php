<?php

namespace BitApps\Social\HTTP\Services\Social\AppInfo;

use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\HTTP\Services\Interfaces\PlatformAppInfoResolverInterface;

class FacebookAppInfoResolver implements PlatformAppInfoResolverInterface
{
    public function getAppInfo($validatedData)
    {
        $httpClient = new HttpClient();

        $appUrl = 'https://graph.facebook.com/' . $validatedData->appKey . '?fields=permissions{permission},roles,name,link,category&access_token=' . $validatedData->appKey . '|' . $validatedData->appSecret;

        $appInfo = $httpClient->request($appUrl, 'GET', []);

        if (isset($appInfo->error)) {
            return (object) ['status' => 'error', 'message' => 'Invalid App Credentials!'];
        }

        return (object) ['status' => 'success', 'appName' => $appInfo->name, 'icon_url' => $appInfo->icon_url];
    }
}
