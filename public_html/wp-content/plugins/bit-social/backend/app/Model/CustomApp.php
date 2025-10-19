<?php

namespace BitApps\Social\Model;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Model;

class CustomApp extends Model
{
    protected $fillable = [
        'id',
        'name',
        'platform',
        'credential',
        'status',
    ];

    protected $casts = [
        'id' => 'int',
    ];

    protected $prefix = Config::VAR_PREFIX;
}
