<?php

namespace BitApps\Social\Model;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Model;

class Log extends Model
{
    public const status = [
        'FAIL'    => 0,
        'SUCCESS' => 1,
    ];

    protected $fillable = [
        'schedule_id',
        'platform',
        'details',
        'status',
    ];

    protected $prefix = Config::VAR_PREFIX;

    protected $casts = [
        'details' => 'object',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'id', 'schedule_id');
    }
}
