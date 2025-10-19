<?php

use BitApps\Social\Deps\BitApps\WPKit\Http\Router\Route;
use BitApps\Social\HTTP\Controllers\DebugLogController;
use BitApps\Social\HTTP\Controllers\RedirectController;

if (!defined('ABSPATH')) {
    exit;
}

Route::get('redirect', [RedirectController::class, 'callback']);

Route::get('plugin-date-from-debug-log', [DebugLogController::class, 'get']);
