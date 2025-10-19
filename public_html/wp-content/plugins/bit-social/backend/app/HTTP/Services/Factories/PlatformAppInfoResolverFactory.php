<?php

namespace BitApps\Social\HTTP\Services\Factories;

class PlatformAppInfoResolverFactory
{
    public function appInfoResolver($platform)
    {
        // Dynamically generate both possible class names
        $proClassName = 'BitApps\\SocialPro\\HTTP\\Services\\Social\\AppInfo\\' . ucfirst($platform) . 'AppInfoResolver';
        $freeClassName = 'BitApps\\Social\\HTTP\\Services\\Social\\AppInfo\\' . ucfirst($platform) . 'AppInfoResolver';

        // Check if either class exists
        if (class_exists($proClassName)) {
            return new $proClassName();
        } elseif (class_exists($freeClassName)) {
            return new $freeClassName();
        }

        // If neither class exists, return an error
        return (object) ['status' => 'error', 'message' => 'File should be created like: ' . ucfirst($platform) . 'AppInfoResolver'];
    }
}
