<?php

namespace BitApps\Social\Providers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\HTTP\Services\Schedule\ScheduleInfo;
use BitApps\Social\HTTP\Services\Schedule\SocialExecution;
use BitApps\Social\Model\Schedule;

final class ScheduleActionHook
{
    public static function register()
    {
        $schedules = Schedule::get(['id', 'config', 'status', 'cron_status', 'started_at']);
        if ($schedules && is_countable($schedules) && \count($schedules) > 0) {
            foreach ($schedules as $schedule) {
                if (!empty($schedule['id']) && $schedule['status'] === Schedule::status['ACTIVE']) {
                    $actionHook = Config::VAR_PREFIX . $schedule['id'] . '_cron_exec';
                    Hooks::addAction($actionHook, [__CLASS__, 'execute']);
                }
            }
        }
    }

    public static function execute($data)
    {
        $social = new SocialExecution(new ScheduleInfo($data));
        $social->publishPost();
    }
}
