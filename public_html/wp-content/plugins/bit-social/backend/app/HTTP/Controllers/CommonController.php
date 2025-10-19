<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Deps\BitApps\WPKit\Http\Response;
use BitApps\Social\HTTP\Requests\ProxyRequest;
use BitApps\Social\Utils\Hash;

final class CommonController
{
    /**
     * Fetch proxy url data.
     *
     * @param ProxyRequest $request
     *
     * @return Response
     */
    public function fetchProxyUrlData(ProxyRequest $request)
    {
        $url = $request['url'];

        $error = $this->isInvalidUrl($url);

        if ($error) {
            return Response::error($error);
        }

        $method = strtoupper($request['method']);

        $headers = $request->get('headers', null);

        $queryParams = $request->get('queryParams', null);

        $bodyParams = $method === 'POST' ? $request->get('bodyParams', []) : [];

        $encrypted = $request->get('encrypted', []);

        if (\count($encrypted) > 0) {
            foreach ($encrypted as $value) {
                $arr = explode('.', $value);

                if (\count($arr) !== 3) {
                    return Response::error('Invalid encrypted value');
                }

                $parentKeyName = $arr[0];

                $childKeyName = $arr[1];

                $indexPosition = $arr[2];

                ${$parentKeyName}[$childKeyName][$indexPosition] = Hash::decrypt(${$parentKeyName}[$childKeyName][$indexPosition]);

                ${$parentKeyName}[$childKeyName] = implode('', ${$parentKeyName}[$childKeyName]);
            }
        }

        if (!\is_null($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        $response = (new HttpClient())->request($url, $method, $bodyParams, $headers);

        if (is_wp_error($response)) {
            return Response::error('Something went wrong');
        }

        return Response::success($response);
    }

    private function isInvalidUrl($url)
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !\in_array($parsedUrl['scheme'], ['http', 'https'], true)) {
            return 'Only HTTP and HTTPS URLs are allowed.';
        }

        if (isset($parsedUrl['host']) && $parsedUrl['host'] === $_SERVER['HTTP_HOST']) {
            return 'Self request is not allowed.';
        }

        return false;
    }
}
