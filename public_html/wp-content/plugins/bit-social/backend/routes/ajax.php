<?php

use BitApps\Social\Deps\BitApps\WPKit\Http\Router\Route;
use BitApps\Social\HTTP\Controllers\AccountController;
use BitApps\Social\HTTP\Controllers\AnalyticsController;
use BitApps\Social\HTTP\Controllers\AuthController;
use BitApps\Social\HTTP\Controllers\AutoPostController;
use BitApps\Social\HTTP\Controllers\ChangelogController;
use BitApps\Social\HTTP\Controllers\CustomAppController;
use BitApps\Social\HTTP\Controllers\LogController;
use BitApps\Social\HTTP\Controllers\PluginImprovementController;
use BitApps\Social\HTTP\Controllers\ProxyController;
use BitApps\Social\HTTP\Controllers\RetryController;
use BitApps\Social\HTTP\Controllers\ScheduleController;
use BitApps\Social\HTTP\Controllers\SettingsController;
use BitApps\Social\HTTP\Controllers\ShareNowController;
use BitApps\Social\HTTP\Controllers\SocialTemplateController;
use BitApps\Social\HTTP\Controllers\UserInfoController;
use BitApps\Social\HTTP\Controllers\WpPostController;

if (!defined('ABSPATH')) {
    exit;
}

Route::noAuth()->group(
    function () {
        Route::post('proxy/route', [ProxyController::class, 'proxyRequest']);

        Route::post('changelog-version/update', [ChangelogController::class, 'updateChangelogVersion']);

        Route::post('authorize', [AuthController::class, 'authorize']);
        Route::get('post-types', [WpPostController::class, 'getPostTypes']);
        Route::get('categories', [WpPostController::class, 'getCategoriesAndTags']);
        Route::post('filtered-posts', [WpPostController::class, 'getFilteredPosts']);

        Route::get('schedule', [ScheduleController::class, 'allSchedule']);
        Route::get('schedule/{page}/{limit}', [ScheduleController::class, 'index']);
        Route::get('schedule/{search}/{page}/{limit}', [ScheduleController::class, 'search']);
        Route::get('schedule/{schedule}', [ScheduleController::class, 'show']);
        Route::post('schedule', [ScheduleController::class, 'store']);
        Route::post('schedule/{schedule}/update', [ScheduleController::class, 'update']);
        Route::post('schedule/{schedule}/update-status', [ScheduleController::class, 'updateStatus']);
        Route::post('schedule/destroy', [ScheduleController::class, 'destroy']);

        Route::get('logs/{page}/{limit}', [LogController::class, 'index']);
        Route::post('logs/destroy/batch', [LogController::class, 'destroy']);
        Route::post('retry', [RetryController::class, 'retry']);

        Route::get('accounts', [AccountController::class, 'index']);
        Route::post('account/save', [AccountController::class, 'store']);
        Route::post('account/{account}/destroy', [AccountController::class, 'destroy']);
        Route::post('account/{account}/account-schedule', [AccountController::class, 'findScheduleByAccountId']);
        Route::post('account/{account}/update-status', [AccountController::class, 'updateStatus']);
        Route::post('account-platform', [AccountController::class, 'accountPlatform']);
        Route::get('active-accounts', [AccountController::class, 'activeAccounts']);
        Route::post('platforms-credentials', [AccountController::class, 'platformsCredentials']);
        Route::post('custom-app', [CustomAppController::class, 'store']);
        Route::get('custom-app', [CustomAppController::class, 'index']);
        Route::post('custom-app/{customApp}/destroy', [CustomAppController::class, 'destroy']);

        Route::get('smart-tags', [ScheduleController::class, 'getSmartTags']);

        Route::post('upload-files', [ShareNowController::class, 'uploadFile']);
        Route::get('all-media', [ShareNowController::class, 'getAllMedia']);
        Route::post('share-now', [ShareNowController::class, 'store']);
        Route::get('share-now/{page}/{limit}', [ShareNowController::class, 'index']);
        Route::post('share-now/{shareNowId}/update', [ShareNowController::class, 'update']);
        Route::post('share-now/destroy/batch', [ShareNowController::class, 'destroy']);
        Route::get('share-now/', [ShareNowController::class, 'update']);

        Route::get('socialTemplates', [SocialTemplateController::class, 'socialTemplates']);
        Route::post('socialTemplates/update', [SocialTemplateController::class, 'update']);

        Route::get('autoPostSettings', [AutoPostController::class, 'autoPostSettings']);
        Route::post('autoPostSettings/update', [AutoPostController::class, 'update']);

        Route::get('analytics', [AnalyticsController::class, 'index']);
        Route::get('user-info', [UserInfoController::class, 'index']);

        Route::get('plugin-improvement', [PluginImprovementController::class, 'getData']);
        Route::post('plugin-improvement', [PluginImprovementController::class, 'createOrUpdate']);

        Route::get('all-settings', [SettingsController::class, 'getAllSettings']);
        Route::post('settings/update', [SettingsController::class, 'updateSettings']);
    }
)->middleware('nonce:admin');

Route::noAuth()->group(function (): void {
    Route::post('auto-post-background-process', [AutoPostController::class, 'executeSocialPost']);
})->middleware('nonce');
