<?php

namespace BitApps\Social\HTTP\Services\Factories;

class AuthServiceFactory
{
    public function createAuthService($platform, $authType)
    {
        $proClassName = 'BitApps\\SocialPro\\HTTP\\Services\\Social\\' . ucfirst($platform) . 'Service\\' . ucfirst($platform) . $authType . 'Service';
        $freeClassName = 'BitApps\\Social\\HTTP\\Services\\Social\\' . ucfirst($platform) . 'Service\\' . ucfirst($platform) . $authType . 'Service';

        if (class_exists($proClassName)) {
            return new $proClassName();
        } elseif (class_exists($freeClassName)) {
            return new $freeClassName();
        }

        return (object) ['status' => 'error', 'message' => 'File should be created like: ' . ucfirst($platform) . $authType . 'Service'];
    }
}
