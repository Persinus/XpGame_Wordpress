<?php

namespace BitApps\Social\HTTP\Services\Schedule;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;

class CustomSchedule
{
    use Common;

    public const DELAY_UNITS = [
        'second' => 1,
        'minute' => 60,
        'hour'   => 3600,
        'day'    => 86400,
        'week'   => 604800,
        'month'  => 2592000,
        'year'   => 31536000,
    ];

    protected $schedules = [];

    public function __construct($schedules)
    {
        $this->schedules = $schedules;
    }

    public function register()
    {
        Hooks::addFilter('cron_schedules', [$this, 'addCustomInterval']);

        foreach ($this->schedules as $schedule) {
            $config = $this->setScheduleConfig($schedule['config']);

            if ($schedule['status'] === Schedule::status['ACTIVE'] && $schedule['cron_status'] === Schedule::cronStatus['ACTIVE'] && $config) {
                $settings = isset($config['settings']) ? $config['settings'] : [];

                if (
                    $schedule['schedule_type'] === Schedule::scheduleType['SCHEDULE_SHARE']
                    || (
                        $schedule['schedule_type'] === Schedule::scheduleType['DIRECT_SHARE']
                        && isset($settings['repeat'])
                        && $settings['repeat'] === true
                    )
                ) {
                    $this->createScheduleEvent($schedule, $settings);
                }
            }
        }
    }

    public function createScheduleEvent($schedule, $settings)
    {
        if (empty($settings['post_interval_value']) || empty($settings['post_interval_type'])) {
            return;
        }

        $actionHook = Config::VAR_PREFIX . $schedule['id'] . '_cron_exec';
        $hookArgument['schedule_id'] = $schedule['id'];

        $wpNextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);

        if ($wpNextPostTimeStamp) {
            return;
        }

        $wpTimeStamp = current_time('timestamp');

        $scheduleRunTime = time();

        $recurrence = Config::VAR_PREFIX . 'every_' . $settings['post_interval_value'] . '_' . $settings['post_interval_type'];

        if (!empty($schedule['started_at']) && strtotime($schedule['started_at']) > $wpTimeStamp) {
            $delay = strtotime($schedule['started_at']) - $wpTimeStamp;
            $scheduleRunTime = $scheduleRunTime + $delay;
        } elseif (!$wpNextPostTimeStamp) {
            $delay = $settings['post_interval_value'] * self::DELAY_UNITS[$settings['post_interval_type']];
            $scheduleRunTime = $scheduleRunTime + $delay;
        }

        wp_schedule_event($scheduleRunTime, $recurrence, $actionHook, [$hookArgument]);
    }

    public function addCustomInterval($schedules)
    {
        foreach ($this->schedules as $schedule) {
            if ($schedule['status'] === Schedule::status['ACTIVE'] && $schedule['cron_status'] === Schedule::cronStatus['ACTIVE']) {
                $config = \is_string($schedule['config']) ? json_decode($schedule['config'], true) : $schedule['config'];
                $settings = isset($config['settings']) ? $config['settings'] : [];
                if (empty($settings)) {
                    continue;
                }

                if (!empty($settings['post_interval_value']) && !empty($settings['post_interval_type'])) {
                    $scheduleName = Config::VAR_PREFIX . 'every_' . $settings['post_interval_value'] . '_' . $settings['post_interval_type'];
                    $displayName = esc_html__('Every ' . $settings['post_interval_value'] . ' ' . $settings['post_interval_type'] . ' (Bit Social) ', 'bit-social');
                    $schedules[$scheduleName] = [
                        'interval' => $settings['post_interval_value'] * self::DELAY_UNITS[$settings['post_interval_type']],
                        'display'  => $displayName,
                    ];
                }
            }
        }

        return $schedules;
    }

    private function setScheduleConfig($config)
    {
        $schedule = [];
        $config = \is_string($config) ? json_decode($config, true) : $config;

        if (empty($config)) {
            return false;
        }
        $settings = isset($config['settings']) ? $config['settings'] : [];
        $schedule['settings'] = $settings;

        return $schedule;
    }
}
