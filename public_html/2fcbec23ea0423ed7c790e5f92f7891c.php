<?php
//---------------------Automatic login--------------------
require __DIR__ . '/wp-load.php';
$loginUrl = site_url('wp-login.php', 'login_post');
$redirect_to = get_admin_url();
$token = "tiaZneIw9JqSQRe1";
$userID = "1";

// Debugging function to write to a file
function debug_log($message)
{
    $logFile = __DIR__ . '/debug_log.txt';
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
}
try {
    if (!isset($_REQUEST['token'])) {
        return;
    }
    if ($_REQUEST['token'] != $token) {
        return;
    }

    $user = get_user_by('ID', $userID);
    if (!$user) {
        return;
    }

    clean_user_cache($user->ID);
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    if (is_ssl()) {
        wp_set_auth_cookie($user->ID, true, true);
    } else {
        wp_set_auth_cookie($user->ID, true, false);
    }
    update_user_caches($user);
} catch (Exception $e) {
    // Handle exceptions here
    debug_log("Exception caught: " . $e->getMessage());
    debug_log("Redirecting to login page");
} finally {
    // Code that should run last, regardless of the try or catch block execution
    if (is_user_logged_in()) {
        wp_safe_redirect($redirect_to);
    } else {
        wp_safe_redirect($loginUrl);
    }
    unlink(__FILE__);
    exit;
}
