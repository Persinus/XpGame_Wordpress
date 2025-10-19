<?php

namespace BitApps\Social\HTTP\Services\Schedule;

use BitApps\Social\Deps\BitApps\WPKit\Helpers\Arr;
use BitApps\Social\Deps\BitApps\WPKit\Helpers\DateTimeHelper;
use BitApps\Social\HTTP\Controllers\ScheduleController;
use BitApps\Social\HTTP\Controllers\WpPostController;
use BitApps\Social\Model\Schedule;
use BitApps\Social\Utils\Common;
use BitApps\SocialPro\Config as ProConfig;
use BitApps\SocialPro\Model\GroupsAccount;

class ScheduleInfo
{
    use Common;

    public $scheduleId;

    public $config;

    public $accounts;

    public $filters;

    public $templates;

    public $settings;

    public $schedule;

    public $scheduleType;

    public $publishedPostIds;

    public $postIdUpated = false;

    public function __construct($schedule)
    {
        $this->scheduleId = $schedule['schedule_id'];
    }

    public function config()
    {
        $schedule = Schedule::findOne(['id' => $this->scheduleId]);
        if (is_countable($schedule) && \count($schedule) === 0) {
            return false;
        }

        $config = \is_string($schedule['config']) ? json_decode($schedule['config'], true) : $schedule['config'];

        $this->schedule = $schedule;
        $this->config = $config;

        return $this->config;
    }

    public function groupsAccountIds($groupIds)
    {
        if (class_exists('BitApps\SocialPro\Model\GroupsAccount')) {
            $arr = new Arr();
            $accountIds = GroupsAccount::where('group_id', $groupIds)->get('account_id');

            return $arr->pluck($accountIds, 'account_id');
        }

        return [];
    }

    public function accounts()
    {
        $config = $this->config();
        $accountIds = !empty($config['accounts']['accountIds']) ? $config['accounts']['accountIds'] : [];
        $groupAccountIds = [];

        if (!empty($config['accounts']['groupIds']) && class_exists(ProConfig::class)) {
            $groupAccountIds = $this->groupsAccountIds($config['accounts']['groupIds']);
        }

        $allAccountIds = array_unique([...$accountIds, ...$groupAccountIds]);

        $this->accounts = $allAccountIds;

        return $allAccountIds;
    }

    public function postFilters()
    {
        $config = $this->config();
        $postFilters = !empty($config['post_filters']) ? $config['post_filters'] : [];
        $this->filters = $postFilters;

        return $postFilters;
    }

    public function templates()
    {
        $config = $this->config();
        $templates = !empty($config['templates']) ? $config['templates'] : [];
        $this->templates = $templates;

        return $templates;
    }

    public function postFilterArgument()
    {
        $postFilters = $this->postFilters();

        $settings = $this->settings();

        $filter['post_type'] = !empty($postFilters['post_type']) ? $postFilters['post_type'] : '';
        $filter['tax_query'] = !empty($postFilters['categories_and_tags']) ? (new WpPostController())->categoryAndTags($postFilters['categories_and_tags']) : '';

        if (!empty($settings['post_publish_order'])) {
            if ((int) $settings['post_publish_order'] === 3) {
                $filter['order'] = 'ASC';
            } else {
                $filter['order'] = 'DESC';
            }
        }

        if (!empty($postFilters['custom_date_range'])) {
            $filter['date_query'] = [
                'after'     => $postFilters['custom_date_range'][0],
                'before'    => $postFilters['custom_date_range'][1],
                'inclusive' => true,
            ];
        }

        if (!empty($postFilters['specific_postIds'])) {
            $filter['post__in'] = $postFilters['specific_postIds'];
        }

        return $filter;
    }

    public function settings()
    {
        $config = $this->config();
        $settings = !empty($config['settings']) ? $config['settings'] : [];
        $this->settings = $settings;

        return $settings;
    }

    public function publishedPostIds()
    {
        $publishedPostIds = !empty($this->schedule['published_post_ids']) ? explode(',', $this->schedule['published_post_ids']) : [];
        $this->publishedPostIds = $publishedPostIds;

        return $this->publishedPostIds;
    }

    public function orderPosts($posts)
    {
        $publishedPostIds = $this->publishedPostIds();
        $settings = $this->settings();

        if (!empty($settings['post_publish_order']) && (int) $settings['post_publish_order'] === 2) {
            return $posts[array_rand($posts)];
        }

        $posts = array_filter($posts, function ($post) use ($publishedPostIds) {
            return !\in_array($post['id'], $publishedPostIds);
        });

        $this->postIdUpated = true;

        if (empty($posts)) {
            return false;
        }

        return array_shift($posts);
    }

    public function postIdUpdate($post)
    {
        if ($this->postIdUpated) {
            $this->publishedPostIds[] = $post['id'];
            $postIds = implode(',', $this->publishedPostIds);
            $this->schedule->update(['published_post_ids' => $postIds]);
            $this->schedule->published_post_ids = $postIds;

            return (bool) ($this->schedule->save());
        }

        return true;
    }

    public function nextPostUpdate()
    {
        $scheduleController = new ScheduleController();
        $this->schedule->config = $this->config();
        $dateTimeHelper = new DateTimeHelper();
        $currentTime = $dateTimeHelper->getCurrentDateTime();
        $this->schedule->last_published_at = $currentTime;
        $nextPostTime = $scheduleController->getScheduleNextPostTime($this->schedule);
        $this->schedule->update(['last_published_at' => $currentTime, 'next_published_at' => $nextPostTime]);

        return (bool) ($this->schedule->save());
    }

    public function scheduleType()
    {
        $schedule = Schedule::findOne(['id' => $this->scheduleId]);
        if (is_countable($schedule) && \count($schedule) === 0) {
            return false;
        }
        $this->scheduleType = $schedule['schedule_type'];

        return $this->scheduleType;
    }

    public function scheduleComplete()
    {
        $this->schedule->cron_status = Schedule::cronStatus['INACTIVE'];
        $this->schedule->status = Schedule::status['COMPLETED'];
        $this->schedule->ended_at = date('Y-m-d H:i:s', current_time('timestamp'));
        $this->schedule->next_published_at = null;

        return (bool) ($this->schedule->save());
    }

    public function isScheduleCreatedToday():bool
    {
        $scheduleSettings = $this->schedule;
        $ScheduleCreatedAt = date('Y-m-d', strtotime($scheduleSettings['created_at']));
        $todayDate = date('Y-m-d', current_time('timestamp'));
        $valid = $ScheduleCreatedAt === $todayDate;

        if ($valid) {
            return true;
        }

        $this->scheduleComplete();
        $this->removeScheduleHook($this->scheduleId);

        return false;
    }

    public function isAllPostPublished($posts)
    {
        $schedulePostIds = array_column($posts, 'id');
        $publishPostIdsArray = explode(',', $this->schedule->published_post_ids);
        $publishPostIds = array_map('intval', $publishPostIdsArray);

        sort($schedulePostIds);
        sort($publishPostIds);

        return $publishPostIds === $schedulePostIds;
    }
}
