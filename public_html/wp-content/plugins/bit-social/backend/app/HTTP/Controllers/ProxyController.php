<?php

namespace BitApps\Social\HTTP\Controllers;

use BitApps\Social\Deps\BitApps\WPKit\Http\Client\HttpClient;
use BitApps\Social\Factories\ProxyRequestParserFactory;
use BitApps\Social\HTTP\Requests\ProxyRequest;
use BitApps\SocialPro\Deps\BitApps\WPKit\Http\Response;

final class ProxyController
{
    /**
     * Make a proxy request.
     *
     * @param ProxyRequest $request
     *
     * @return Response
     */
    public function proxyRequest(ProxyRequest $request)
    {
        $validated = ProxyRequestParserFactory::parse($request->validated());

        $url = $validated['url'];
        $method = strtoupper($validated['method']);
        $headers = $validated['headers'] ?? null;
        $queryParams = $validated['queryParams'] ?? null;
        $bodyParams = $method === 'POST' ? $validated['bodyParams'] ?? [] : [];

        $error = $this->isInvalidURL($url);

        if ($error) {
            return Response::error($error);
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

    /**
     * check if the URL is invalid.
     *
     * @param string $url
     *
     * @return string|bool
     */
    private function isInvalidURL($url)
    {
        $parsedUrl = parse_url($url);

        if ($parsedUrl === false || !\in_array($parsedUrl['scheme'], ['http', 'https'], true)) {
            return 'Only HTTP and HTTPS URLs are allowed.';
        }

        if (isset($parsedUrl['host']) && $parsedUrl['host'] === site_url()) {
            return 'Self request is not allowed.';
        }

        return false;
    }
}
