<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;

class ChangelogController
{
    public function updateChangelogVersion(Request $request)
    {
        Config::updateOption('changelog_version', $request->version);

        return Response::success('Changelog version updated.');
    }
}
