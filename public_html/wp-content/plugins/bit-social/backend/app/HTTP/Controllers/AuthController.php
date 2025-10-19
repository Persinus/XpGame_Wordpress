<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Requests\AuthorizeRequest;
use BitApps\Social\HTTP\Services\Factories\AuthServiceFactory;

class AuthController
{
    private $authFactory;

    public function __construct()
    {
        $authFactory = new AuthServiceFactory();
        $this->authFactory = $authFactory;
    }

    public function authorize(AuthorizeRequest $request)
    {
        $authConfig = $request->all()['config'];
        $platform = $authConfig['platform'];
        $authType = $authConfig['authType'];

        $authService = $this->authFactory->createAuthService($platform, $authType);
        if (property_exists($authService, 'status') && $authService->status === 'error') {
            return Response::error($authService);
        }

        $response = $authService->authHandler($request);

        if (isset($response->status) && $response->status === 'error') {
            return Response::error($response);
        }

        return Response::success($response);
    }
}
