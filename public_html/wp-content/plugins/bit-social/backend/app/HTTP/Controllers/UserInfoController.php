<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Response;

final class UserInfoController
{
    public function index()
    {
        $current_user = wp_get_current_user();

        $userInfo = [
            'username'   => $current_user->user_login ?? '',
            'user_email' => $current_user->user_email ?? '',
            'first_name' => $current_user->user_firstname ?? '',
        ];

        return Response::success($userInfo);
    }
}
