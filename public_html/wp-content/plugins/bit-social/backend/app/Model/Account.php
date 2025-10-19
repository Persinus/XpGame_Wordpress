<?php

namespace BitApps\Social\Model;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPDatabase\Model;

class Account extends Model
{
    public const accountType = [
        'DEFAULT' => 1,
        'CUSTOM'  => 2,
    ];

    public const ACCOUNT_STATUS = [
        'active'   => 1,
        'inactive' => 0,
    ];

    protected $prefix = Config::VAR_PREFIX;

    protected $fillable = [
        'profile_id',
        'account_id',
        'account_name',
        'details',
        'platform',
        'account_type',
        'status',
    ];

    protected $casts = [
        'id'           => 'int',
        'account_type' => 'int',
        'status'       => 'int',
        'details'      => 'object'
    ];
}
