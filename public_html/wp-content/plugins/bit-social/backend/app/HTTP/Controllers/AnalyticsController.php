<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Model\Account;
use BitApps\Social\Model\Log;
use BitApps\Social\Model\Schedule;

final class AnalyticsController
{
    public function index()
    {
        $activeAccountCount = Account::where('status', true)->count();
        $publishedPostCount = Log::where('status', true)->count();
        $activeScheduleCount = Schedule::where('status', true)->count();

        $analyticsData = [
            'active_account_count'  => $activeAccountCount ?? 0,
            'published_post_count'  => $publishedPostCount ?? 0,
            'active_schedule_count' => $activeScheduleCount ?? 0,
        ];

        return Response::success($analyticsData);
    }
}
