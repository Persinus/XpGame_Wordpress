<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;

class RedirectController
{
    public function callback(Request $request)
    {
        $params = $request->all();

        if (isset($params['oauth_verifier'])) {
            unset($params['oauth_token']);
        } elseif (!isset($params['code'])) {
            return Response::error('Invalid Code');
        }

        $this->redirectToState($params);
    }

    private function redirectToState($params)
    {
        if (strpos('https', $params['state'])) {
            $mySiteUrl = $params['state'];
        } else {
            $mySiteUrl = Config::get('BASE_AUTH_STATE_URL') . '#/accounts/auth/response';
        }

        if (isset($params['state'])) {
            unset($params['state']);
        }

        if (strpos($mySiteUrl, Config::get('SITE_URL')) === false) {
            return Response::error('Invalid Site URL');
        }

        if (wp_redirect($mySiteUrl . '&' . http_build_query($params), 302)) {
            exit;
        }
    }
}
