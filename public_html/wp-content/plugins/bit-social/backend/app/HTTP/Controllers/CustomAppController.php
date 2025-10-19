<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Services\Factories\PlatformAppInfoResolverFactory;
use BitApps\Social\Model\CustomApp;
use BitApps\Social\Utils\Hash;
use BitApps\SocialPro\Config as ProConfig;

class CustomAppController
{
    private $appInfoResolverFactory;

    public function __construct()
    {
        $this->appInfoResolverFactory = new PlatformAppInfoResolverFactory();
    }

    public function store(Request $request)
    {
        $validationRules = [
            'appKey'      => ['required', 'string'],
            'appSecret'   => ['required', 'string'],
            'platform'    => ['required', 'string'],
            'redirectUri' => ['required', 'string'],
        ];

        if (class_exists(ProConfig::class) && is_plugin_active(ProConfig::get('BASENAME'))) {
            $validationRules = apply_filters(ProConfig::VAR_PREFIX . 'validation_check', $validationRules, $request->platform);
        }

        $validatedData = (object) $request->validate($validationRules);

        $appInfoResolver = $this->appInfoResolverFactory->appInfoResolver($validatedData->platform);

        if (property_exists($appInfoResolver, 'status') && $appInfoResolver->status === 'error') {
            return Response::error(['message' => $appInfoResolver->message]);
        }

        $appInfo = $appInfoResolver->getAppInfo($validatedData);

        if (property_exists($appInfo, 'status') && $appInfo->status === 'error') {
            return Response::error(['message' => $appInfo->message]);
        }

        $appName = $appInfo->appName;
        $platform = $validatedData->platform;

        $isAppExist = CustomApp::where('name', $appName)->where('platform', $platform)->first();

        if ($isAppExist) {
            return Response::error(['data' => null, 'message' => 'This app already exists!']);
        }

        $apiVersion = $validatedData->platform === 'twitter' ? $validatedData->apiVersion : null;

        $customAppData = [
            'name'       => $appName,
            'platform'   => $platform,
            'credential' => Hash::encrypt(json_encode([
                'appKey'      => $validatedData->appKey,
                'appSecret'   => $validatedData->appSecret,
                'redirectUri' => $validatedData->redirectUri,
                'icon_url'    => $appInfo->icon_url ?? null,
                'apiVersion'  => $apiVersion
            ])),
            'status' => 1,
        ];

        if (CustomApp::insert($customAppData)) {
            return Response::success(['data' => $customAppData, 'message' => 'Custom App created successfully']);
        }

        return Response::error(['data' => null, 'message' => 'Custom App creation failed']);
    }

    public function index(Request $request)
    {
        $platform = $request->platform;

        $customApps = CustomApp::where('platform', $platform)->get();

        $customAppList = [];

        if (\is_array($customApps)) {
            foreach ($customApps as $customApp) {
                $platformCredential = $this->extractCredentials($customApp->credential);

                $customApp = [
                    'id'         => $customApp->id,
                    'name'       => $customApp->name,
                    'platform'   => $customApp->platform,
                    'credential' => $platformCredential,
                    'status'     => $customApp->status,
                ];

                $customAppList[] = $customApp;
            }
        }

        if (empty($customAppList)) {
            return Response::success($customAppList);
        }

        return Response::success($customAppList);
    }

    public function extractCredentials($details)
    {
        $credentials = Hash::decrypt($details);

        $credentialsData = json_decode($credentials);

        return [
            'appKey'      => $credentialsData->appKey,
            'appSecret'   => $credentialsData->appSecret,
            'redirectUri' => $credentialsData->redirectUri,
            'icon_url'    => isset($credentialsData->icon_url) ? $credentialsData->icon_url : '',
            'apiVersion'  => $credentialsData->apiVersion
        ];
    }

    public function destroy(CustomApp $customApp)
    {
        if ($customApp->delete()) {
            return Response::success('Custom App deleted successfully!');
        }

        return Response::error('Custom App deletion failed!');
    }
}
