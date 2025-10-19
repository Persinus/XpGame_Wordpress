<?php

namespace BitApps\Social\Model;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Model;

class Schedule extends Model
{
    public const status = [
        'INACTIVE'  => 0,
        'ACTIVE'    => 1,
        'COMPLETED' => 2,
        'DRAFT'     => 3,
        'MISSED'    => 4
    ];

    public const cronStatus = [
        'INACTIVE' => 0,
        'ACTIVE'   => 1,
    ];

    public const scheduleType = [
        'SCHEDULE_SHARE' => 1,
        'DIRECT_SHARE'   => 2,
    ];

    protected $fillable = [
        'name',
        'config',
        'published_post_ids',
        'repeat_schedule',
        'schedule_type',
        'status',
        'started_at',
        'last_published_at',
        'next_published_at',
        'cron_status',
    ];

    protected $casts = [
        'id'            => 'int',
        'status'        => 'int',
        'cron_status'   => 'int',
        'schedule_type' => 'int',
        'config'        => 'array',
    ];

    protected $prefix = Config::VAR_PREFIX;

    public function logs()
    {
        return $this->hasMany(Log::class, 'schedule_id', 'id');
    }
}
