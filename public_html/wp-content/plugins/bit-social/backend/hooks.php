<?php

use BitApps\Social\Deps\BitApps\WPKit\Hooks\Hooks;
use BitApps\Social\HTTP\Controllers\AutoPostController;

if (!defined('ABSPATH')) {
    exit;
}

// Hooks::addAction('wp_after_insert_post', function ($postId, $post, $update, $postBefore) {
//     (new AutoPostController())->publishWpPost($postId, $post, $update, $postBefore);
//     // error_log('Auto post hook called');
// }, 10, 4);

Hooks::addAction('wp_after_insert_post', [new AutoPostController(), 'publishWpPost'], 10, 4);
