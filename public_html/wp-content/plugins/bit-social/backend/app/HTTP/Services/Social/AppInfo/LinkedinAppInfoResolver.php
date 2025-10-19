<?php

namespace BitApps\Social\HTTP\Services\Social\AppInfo;

use BitApps\Social\HTTP\Services\Interfaces\PlatformAppInfoResolverInterface;

class LinkedinAppInfoResolver implements PlatformAppInfoResolverInterface
{
    public function getAppInfo($validatedData)
    {
        if (!$validatedData->appKey) {
            return (object) ['status' => 'error', 'message' => 'Invalid App Credentials!'];
        }

        // there is no app name so, api key used as app name
        return (object) ['status' => 'success', 'appName' => $validatedData->appKey];
    }
}
