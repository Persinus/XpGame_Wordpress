<?php

namespace BitApps\Social\HTTP\Middleware;

use BitApps\Social\Config;
use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;

final class NonceCheckerMiddleware
{
    public function handle(Request $request)
    {
        if (!$request->has('_ajax_nonce') || !wp_verify_nonce(sanitize_key($request->_ajax_nonce), Config::withPrefix('nonce'))) {
            return Response::error('Invalid token')->httpStatus(411);
        }

        return true;
    }
}
