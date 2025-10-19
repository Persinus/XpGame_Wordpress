<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Deps\BitApps\WPTelemetry\Telemetry\Telemetry;

final class PluginImprovementController
{
    public function getData()
    {
        return Response::success([
            'allowTracking' => Config::getOption('allow_tracking'),
        ]);
    }

    public function createOrUpdate(Request $request)
    {
        $validatedData = (object) $request->validate([
            'allowTracking' => ['required', 'boolean'],
        ]);

        if ($validatedData->allowTracking) {
            Telemetry::report()->trackingOptIn();
        } else {
            Telemetry::report()->trackingOptOut();
        }

        return Response::success([
            'allowTracking' => $validatedData->allowTracking,
        ]);
    }
}
