<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;

class ShareNowController
{
    use Common;

    public function index($page, $limit)
    {
        $shareNowData = Schedule::where('schedule_type', Schedule::scheduleType['DIRECT_SHARE'])
            ->select(['id', 'name', 'config', 'started_at', 'next_published_at', 'status', 'created_at'])
            ->desc()
            ->paginate($page, $limit);

        $missedScheduleIds = [];

        foreach ($shareNowData['data'] as $data) {
            $actionHook = Config::VAR_PREFIX . $data->id . '_cron_exec';
            $hookArgument['schedule_id'] = $data->id;
            $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);

            $settings = $data->config['settings'];
            $repeat = $settings['repeat'] ?? false;
            $isStatusActive = $data->status === Schedule::status['ACTIVE'];
            $addTwoMinutes = 120;

            // Check share now missed post

            if ($isStatusActive && !$repeat && !empty($data->started_at) && strtotime($data->started_at) + $addTwoMinutes < current_time('timestamp')) {
                $missedScheduleIds[] = $data->id;
            }

            if ($nextPostTimeStamp) {
                $nextPostTimeStamp = wp_next_scheduled($actionHook, [$hookArgument]);
                $exactDateTime = get_date_from_gmt(date('Y-m-d H:i:s', $nextPostTimeStamp), 'Y-m-d H:i:s');
                $nextPostTimeStamp = strtotime($exactDateTime);

                $currentTimestamp = current_time('timestamp');

                $humanReadableTime = human_time_diff($currentTimestamp, $nextPostTimeStamp);

                $data->human_readable_next_publish = 'in ' . $humanReadableTime;
                $data->next_published_at = $exactDateTime;
            }
        }

        if (!empty($missedScheduleIds)) {
            global $wpdb;
            $table = Config::get('WP_DB_PREFIX') . Config::VAR_PREFIX . 'schedules';
            $missedStatus = Schedule::status['MISSED'];

            $placeholders = implode(',', array_fill(0, \count($missedScheduleIds), '%d'));
            $query = "UPDATE {$table} SET status = %d WHERE id IN ({$placeholders})";

            $response = $wpdb->query($wpdb->prepare($query, $missedStatus, ...$missedScheduleIds));

            if ($response) {
                $this->removeScheduleHook($missedScheduleIds);
            }
        }

        return Response::success($shareNowData);
    }

    public function shareNowData($request): array
    {
        return [
            'name'   => $request->settings['name'],
            'config' => [
                'settings'  => $request->settings,
                'accounts'  => $request->accounts,
                'templates' => $request->templates,
            ],
            'schedule_type' => Schedule::scheduleType['DIRECT_SHARE'],
            'status'        => (isset($request->isDraft) && $request->isDraft) ? Schedule::status['DRAFT'] : Schedule::status['ACTIVE'],
            'started_at'    => $request->settings['started_at'] ?? null,
        ];
    }

    public function store(Request $request)
    {
        $settings = $request->settings;
        $request->settings = $settings; // NOTE: This swapping and value assigning process should be improved

        $shareNowData = $this->shareNowData($request);

        $message = isset($request->isDraft) ? 'Saved as draft successfully' : 'Schedule created successfully';

        $startedAtTimestamp = !empty($shareNowData['started_at']) ? strtotime($shareNowData['started_at']) : false;
        $currentTimestamp = current_time('timestamp');

        if ($startedAtTimestamp && $currentTimestamp < $startedAtTimestamp) {
            $shareNowData['next_published_at'] = $shareNowData['started_at'];
        } else {
            $shareNowData['next_published_at'] = null;
        }

        $schedule = Schedule::insert($shareNowData);

        if (!$schedule) {
            return Response::error('Something went wrong! please try again.');
        }

        // single event schedule, no repeat, no post interval, only start date
        $this->createSingleEventScheduleIsNotRepeat($schedule);

        return Response::success($message);
    }

    public function update(Request $request)
    {
        $settings = $request->settings;
        $request->settings = $settings; // NOTE: This swapping and value assigning process should be improved

        $shareNowData = $this->shareNowData($request);

        if (isset($request->isDraft)) {
            $shareNowData['status'] = Schedule::status['DRAFT'];
        }

        $schedule = Schedule::findOne(['id' => $request->id]);

        if (!$schedule) {
            return Response::error('Something went wrong! Schedule could not be found!');
        }

        $startedAt = $shareNowData['config']['settings']['started_at'] ?? null;

        if (empty($startedAt)) {
            $schedule->update($shareNowData)->save();
            $this->createSingleEventScheduleIsNotRepeat($schedule);

            return Response::success('Share now updated successfully');
        }

        $startedAtTimestamp = !empty($startedAt) ? strtotime($startedAt) : false;
        $currentTimestamp = current_time('timestamp');

        if ($startedAtTimestamp && $startedAtTimestamp > $currentTimestamp) {
            $shareNowData['next_published_at'] = $startedAt;
        } else {
            $shareNowData['next_published_at'] = null;
        }

        $copyShareNowData = $shareNowData['config']['settings'];
        $copyScheduleData = $schedule['config']['settings'];

        unset($copyScheduleData['name'], $copyShareNowData['name']);

        $areEqual = (serialize($copyScheduleData) === serialize($copyShareNowData));
        $schedule->update($shareNowData)->save();

        $message = 'Share now updated successfully';

        if ($schedule['status'] === Schedule::status['ACTIVE'] && !$areEqual) {
            $this->removeScheduleHook($schedule->id);

            if (isset($schedule['config']['settings']['repeat']) && !$schedule['config']['settings']['repeat']) {
                $this->createSingleEventScheduleIsNotRepeat($schedule);

                return;
            }

            $message = 'Schedule published successfully';
        } elseif ($schedule['status'] === Schedule::status['DRAFT']) {
            $this->removeScheduleHook($schedule->id);

            $message = 'Schedule drafted successfully';
        }

        return Response::success($message);
    }

    public function uploadFile(Request $request)
    {
        $requestFiles = $request->files();

        if ($requestFiles) {
            $files = [];
            $fileArray = $requestFiles['file'];

            foreach ($fileArray['name'] as $index => $name) {
                $files[] = [
                    'name'     => $name,
                    'type'     => $fileArray['type'][$index],
                    'tmp_name' => $fileArray['tmp_name'][$index],
                    'error'    => $fileArray['error'][$index],
                    'size'     => $fileArray['size'][$index]
                ];
            }

            foreach ($files as $file) {
                $upload_dir = wp_upload_dir();
                $uuid = wp_generate_uuid4();
                $file_name = $file['name'];
                $filename_without_ext = pathinfo($file_name, PATHINFO_FILENAME);
                $file_path = $upload_dir['path'] . '/' . $uuid . '-' . $file_name;

                // Move the uploaded file to the destination directory
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // Create an attachment post
                    $attachmentData = [
                        'post_mime_type' => $file['type'],
                        'post_title'     => sanitize_file_name($filename_without_ext),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    ];
                    $attachment_id = wp_insert_attachment($attachmentData, $file_path);
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);
                }
            }
        }

        return Response::success([]);
    }

    public function getAllMedia()
    {
        $media = [];
        $args = [
            'post_type'   => 'attachment',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => null,
        ];

        $attachments = get_posts($args);

        if ($attachments) {
            foreach ($attachments as $post) {
                $imageSource = wp_get_attachment_url($post->ID);

                $imageSource = wp_get_attachment_url($post->ID);
                $attachmentMetaData = wp_get_attachment_metadata($post->ID);

                if (isset($attachmentMetaData['mime_type']) && $attachmentMetaData['mime_type'] !== 'image/gif') {
                    $output['mimetype'] = $attachmentMetaData['mime_type'] ?? null;
                } else {
                    $output['mimetype'] = $attachmentMetaData['sizes']['medium']['mime-type'] ?? null;
                }
                $output['url'] = is_ssl() ? preg_replace('#^http://#', 'https://', $imageSource) : $imageSource;
                $output['width'] = $attachmentMetaData['width'] ?? null;
                $output['height'] = $attachmentMetaData['height'] ?? null;
                $output['filesize'] = $attachmentMetaData['filesize'] ?? null;
                $output['dimension'] = $attachmentMetaData['width'] * $attachmentMetaData['height'];

                $media[] = $output;
            }
        }
        if ($media) {
            if (\count($media) === 0) {
                return Response::success('No media');
            }

            return Response::success($media);
        }

        return Response::error([]);
    }

    public function destroy(Request $request)
    {
        Schedule::whereIn('id', $request->shareNowIds)->delete();

        $this->removeScheduleHook($request->shareNowIds);

        return Response::success('Selected shareNowIds deleted');
    }

    public function createSingleEventScheduleIsNotRepeat($schedule)
    {
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

        // When share now start date undefined

        if (empty($schedule['started_at'])) {
            $schedule->update(['started_at' => date('Y-m-d H:i:s', $wpTimeStamp)])->save();
        }

        return wp_schedule_single_event($scheduleRunTime, $actionHook, [$hookArgument]);
    }
}
