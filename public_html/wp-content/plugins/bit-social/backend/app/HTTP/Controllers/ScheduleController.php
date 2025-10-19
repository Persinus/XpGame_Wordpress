<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Requests\ScheduleStoreRequest;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;
use BitApps\Social\Utils\SmartTag;
use DateTime;

class ScheduleController
{
    use Common;

    public function index($page, $limit)
    {
        $schedules = Schedule::where('schedule_type', Schedule::scheduleType['SCHEDULE_SHARE'])
            ->select(['id', 'name', 'config', 'started_at', 'next_published_at', 'status', 'created_at'])
            ->desc()
            ->paginate($page, $limit);

        foreach ($schedules['data'] as $schedule) {
            $actionHook = Config::VAR_PREFIX . $schedule->id . '_cron_exec';
            $hookArgument['schedule_id'] = $schedule->id;
            $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);

            if ($nextPostTimeStamp) {
                $actionHook = Config::VAR_PREFIX . $schedule->id . '_cron_exec';
                $hookArgument['schedule_id'] = $schedule->id;

                $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);
                $exactDateTime = get_date_from_gmt(date('Y-m-d H:i:s', $nextPostTimeStamp), 'Y-m-d H:i:s');
                $nextPostTimeStamp = strtotime($exactDateTime);

                $currentTimestamp = current_time('timestamp');

                $humanReadableTime = human_time_diff($currentTimestamp, $nextPostTimeStamp);

                $schedule->human_readable_next_publish = 'in ' . $humanReadableTime;
                $schedule->next_published_at = $exactDateTime;
            }
        }

        return Response::success($schedules);
    }

    public function search(Request $request, $search, $page, $limit)
    {
        $validatedData = (object) $request->validate([
            'search' => ['required', 'string'],
            'page'   => ['required', 'integer'],
            'limit'  => ['required', 'integer'],
        ]);

        $schedules = Schedule::where('name', 'like', '%' . $validatedData->search . '%')->where('schedule_type', Schedule::scheduleType['SCHEDULE_SHARE'])
            ->select(['id', 'name', 'config', 'started_at', 'next_published_at', 'status', 'created_at'])
            ->desc()
            ->paginate($validatedData->page, $validatedData->limit);

        return Response::success($schedules);
    }

    public function show(Schedule $schedule)
    {
        if ($schedule->exists()) {
            return $schedule;
        }

        return Response::error($schedule);
    }

    public function store(ScheduleStoreRequest $request)
    {
        $scheduleData = [
            'name'   => $request->settings['name'],
            'config' => [
                'settings'     => $request->settings,
                'post_filters' => $request->post_filters,
                'accounts'     => $request->accounts,
                'templates'    => $request->templates,
            ],
            'schedule_type' => Schedule::scheduleType['SCHEDULE_SHARE'],
            'status'        => Schedule::status['ACTIVE'],
            'started_at'    => $request->settings['started_at'],
        ];

        $startedAtTimestamp = !empty($scheduleData['started_at']) ? strtotime($scheduleData['started_at']) : false;
        $currentTimestamp = current_time('timestamp');

        if ($startedAtTimestamp && $currentTimestamp < $startedAtTimestamp) {
            $scheduleData['next_published_at'] = $scheduleData['started_at'];
        } else {
            $scheduleData['next_published_at'] = null;
        }

        Schedule::insert($scheduleData);

        return Response::success('Schedule created successfully');
    }

    public function update(ScheduleStoreRequest $request, Schedule $schedule)
    {
        $scheduleData = [
            'name'   => $request->settings['name'],
            'config' => [
                'settings'     => $request->settings,
                'post_filters' => $request->post_filters,
                'accounts'     => $request->accounts,
                'templates'    => $request->templates,
            ],
            'started_at' => $request->settings['started_at'],
        ];

        $scheduleData['last_published_at'] = $schedule->last_published_at;

        $startedAtTimestamp = !empty($scheduleData['started_at']) ? strtotime($scheduleData['started_at']) : false;
        $currentTimestamp = current_time('timestamp');

        if ($startedAtTimestamp && $currentTimestamp < $startedAtTimestamp) {
            $scheduleData['next_published_at'] = $scheduleData['started_at'];
        } else {
            $scheduleData['next_published_at'] = null;
        }

        unset($scheduleData['last_published_at']);

        $copyNewScheduleData = $scheduleData['config']['settings'];
        $copyOldScheduleData = $schedule['config']['settings'];

        unset($copyOldScheduleData['name'], $copyNewScheduleData['name']);

        $areEqual = (serialize($copyOldScheduleData) === serialize($copyNewScheduleData));

        $currentSchedule = \is_string($schedule->config) ? json_decode($schedule->config, true) : $schedule->config;

        if ($currentSchedule['post_filters'] !== $request->post_filters) {
            $scheduleData['published_post_ids'] = null;
            $scheduleData['cron_status'] = Schedule::cronStatus['ACTIVE'];
        }

        $schedule->update($scheduleData);

        if ($schedule->save()) {
            if ($schedule['status'] = Schedule::status['ACTIVE'] && !$areEqual) {
                $this->removeScheduleHook($schedule['id']);
            }

            return Response::success('Schedule updated');
        }

        return Response::error('Schedule update failed');
    }

    public function updateStatus(Request $request, Schedule $schedule)
    {
        $validatedData = (object) $request->validate([
            'status' => ['required', 'integer'],
        ]);

        $requestedStatus = $validatedData->status;

        $message = $requestedStatus === 1 ? 'active' : 'paused';

        if ($requestedStatus === $schedule->status) {
            return Response::success('Schedule already ' . $message . '!');
        }

        if ($schedule->status === Schedule::status['COMPLETED']) {
            return Response::error('Schedule already completed');
        }

        $settings = $schedule->config['settings'];
        $repeat = $settings['repeat'] ?? false;

        if ((int) $schedule->schedule_type === Schedule::scheduleType['DIRECT_SHARE'] && !$repeat && strtotime($schedule->started_at) < current_time('timestamp')) {
            $savedSchedule = $schedule->update(['status' => Schedule::status['MISSED']]);

            if ($savedSchedule->save()) {
                $this->removeScheduleHook($schedule['id']);

                return Response::error('Oops! Missed the start post time! Please, update start time or create new one!');
            }
        } else {
            $savedSchedule = $schedule->update(['status' => $requestedStatus]);
        }

        if ($savedSchedule->save()) {
            if ($requestedStatus === Schedule::status['INACTIVE']) {
                $this->removeScheduleHook($schedule['id']);
            } elseif (Schedule::scheduleType['DIRECT_SHARE'] === (int) $schedule->schedule_type && $requestedStatus === Schedule::status['ACTIVE'] && !$repeat) {
                $this->createSingleEventScheduleIsNotRepeat($schedule);
            } elseif ($requestedStatus === Schedule::status['ACTIVE'] && $repeat) {
                $this->removeScheduleHook($schedule->id);
            }

            return Response::success('Schedule status updated');
        }

        return Response::error('Schedule status update failed');
    }

    public function destroy(Request $request)
    {
        Schedule::whereIn('id', $request->scheduleIds)->delete();

        $this->removeScheduleHook($request->scheduleIds);

        return Response::success('Selected schedules deleted');
    }

    public function getScheduleNextPostTime($schedule)
    {
        $schedule = json_decode(json_encode($schedule));
        $config = $schedule->config;

        if (isset($config->post_filters)) {
            $postsFilterDays = $config->post_filters->filter_by_days; // 0 - all times
            $postsOrderType = $config->settings->post_publish_order; // 2 - randomly

            $postsCount = -1;

            if ($postsFilterDays !== '0' && $postsOrderType !== '2') {
                $postsController = new WpPostController();
                $filteredPosts = $postsController->filterPosts($config->post_filters);
                $postsCount = \count($filteredPosts);
            }

            $hasPostsCountLimit = $postsCount !== -1;

            if ($hasPostsCountLimit) {
                if ($postsCount === 0) {
                    return;
                }

                if (isset($schedule->published_post_ids) && \is_array($schedule->published_post_ids) && $postsCount <= \count($schedule->published_post_ids)) {
                    return;
                }
            }
        }

        if (isset($schedule->last_published_at) && !\is_null($schedule->last_published_at)) {
            $nextPostTime = $schedule->last_published_at;
        } else {
            $nextPostTime = $schedule->started_at;
        }

        $interval = [
            'every' => (int) $config->settings->post_interval_value,
            'unit'  => $config->settings->post_interval_type
        ];

        $sleepTime = [];
        $sleepDays = [];

        if (isset($config->settings->sleep_time) && !empty($config->settings->sleep_time)) {
            $start = $config->settings->sleep_time[0];
            $end = $config->settings->sleep_time[1];

            $sleepTime = [
                'start' => $start,
                'end'   => $end
            ];
        }
        if (isset($config->settings->sleep_days) && !empty($config->settings->sleep_days)) {
            $sleepDays = $config->settings->sleep_days;
        }

        return $this->getNextInterval($nextPostTime, $interval, $sleepTime, $sleepDays);
    }

    public function allSchedule()
    {
        $schedules = Schedule::get(['id', 'name', 'config', 'schedule_type', 'started_at', 'next_published_at', 'status', 'created_at']);

        foreach ($schedules as $schedule) {
            $actionHook = Config::VAR_PREFIX . $schedule->id . '_cron_exec';
            $hookArgument['schedule_id'] = $schedule->id;
            $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);

            if ($nextPostTimeStamp) {
                $actionHook = Config::VAR_PREFIX . $schedule->id . '_cron_exec';
                $hookArgument['schedule_id'] = $schedule->id;

                $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);
                $exactDateTime = get_date_from_gmt(date('Y-m-d H:i:s', $nextPostTimeStamp), 'Y-m-d H:i:s');
                $nextPostTimeStamp = strtotime($exactDateTime);

                $currentTimestamp = current_time('timestamp');

                $humanReadableTime = human_time_diff($currentTimestamp, $nextPostTimeStamp);

                $schedule->human_readable_next_publish = 'in ' . $humanReadableTime;
                $schedule->next_published_at = $exactDateTime;

                $schedules['data'][] = $schedule;
            }
        }

        $schedules = $schedules['data'] ?? [];

        return Response::success($schedules);
    }

    public function getSmartTags()
    {
        if (SmartTag::$smartTagNameList) {
            return Response::success(SmartTag::$smartTagNameList);
        }

        return Response::error('Smart tag error');
    }

    private function getNextInterval($startDate, $interval, $sleepTime, $sleepDays)
    {
        $currentDate = new DateTime($startDate);

        switch ($interval['unit']) {
            case 'second':
                $currentDate->modify("+{$interval['every']} seconds");

                break;
            case 'minute':
                $currentDate->modify("+{$interval['every']} minutes");

                break;
            case 'hour':
                $currentDate->modify("+{$interval['every']} hours");

                break;
            case 'day':
                $currentDate->modify("+{$interval['every']} days");

                break;
            case 'week':
                $currentDate->modify("+{$interval['every']} weeks");

                break;
            case 'month':
                $currentDate->modify("+{$interval['every']} months");

                break;
            case 'year':
                $currentDate->modify("+{$interval['every']} years");

                break;
            default:
                break;
        }

        $weekDay = $currentDate->format('D');
        $isSleepDay = $sleepDays && \in_array($weekDay, $sleepDays);
        $isSleepTime = false;

        if ($sleepTime && $sleepTime['start'] && $sleepTime['end']) {
            $sleepStartTime = new DateTime($currentDate->format('Y-m-d'));
            $sleepEndTime = new DateTime($currentDate->format('Y-m-d'));

            list($startHour, $startMinute, $startSecond) = explode(':', $sleepTime['start']);
            list($endHour, $endMinute, $endSecond) = explode(':', $sleepTime['end']);

            $sleepStartTime->setTime($startHour, $startMinute, $startSecond);
            $sleepEndTime->setTime($endHour, $endMinute, $endSecond);

            if ($currentDate >= $sleepStartTime && $currentDate <= $sleepEndTime) {
                $isSleepTime = true;
            }
        }
        if (!$isSleepDay && !$isSleepTime) {
            return $currentDate->format('Y-m-d H:i:s');
        }

        if (!$interval['every']) {
            return $currentDate->format('Y-m-d H:i:s');
        }

        return $this->getNextInterval($currentDate->format('Y-m-d H:i:s'), $interval, $sleepTime, $sleepDays);
    }

    private function createSingleEventScheduleIsNotRepeat($schedule)
    {
        if (isset($schedule['config']['settings']['repeat']) && $schedule['config']['settings']['repeat'] === true) {
            return;
        }

        $scheduleRunTime = time();
        $wpTimeStamp = current_time('timestamp');
        $actionHook = Config::VAR_PREFIX . $schedule['id'] . '_cron_exec';
        $hookArgument['schedule_id'] = $schedule['id'];

        $wpNextSchedule = wp_next_scheduled($actionHook, [$hookArgument]); // it returns timestamp value or false

        if (!empty($schedule['started_at']) && strtotime($schedule['started_at']) > $wpTimeStamp) {
            $delay = strtotime($schedule['started_at']) - $wpTimeStamp;
            $scheduleRunTime = $scheduleRunTime + $delay;
        } elseif ($wpNextSchedule && !empty($schedule['started_at']) && strtotime($schedule['started_at']) < $wpTimeStamp) {
            $scheduleRunTime = $wpNextSchedule;
        }

        return wp_schedule_single_event($scheduleRunTime, $actionHook, [$hookArgument]);
    }
}
