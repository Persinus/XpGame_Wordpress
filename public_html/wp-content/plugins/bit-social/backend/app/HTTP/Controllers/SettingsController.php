<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Requests\SettingsUpdateRequest;

class SettingsController
{
    public $defaultSettings = [
        'settings' => [
            'cron' => ['isDemoCronEnabled' => false],
        ],
        'proSettings' => [
            'cron' => ['isExternalCronEnabled' => false],

        ],
    ];

    public function getAllSettings()
    {
        $settings = Config::getOption('settings', $this->defaultSettings['settings']);
        $proSettings = Config::getOption('pro_settings', $this->defaultSettings['proSettings']);

        $allSettings = [
            'settings'    => $settings,
            'proSettings' => $proSettings
        ];

        return Response::success($allSettings);
    }

    public function updateSettings(SettingsUpdateRequest $request)
    {
        // Update general settings
        // $settings = $request->settings;
    }
}
