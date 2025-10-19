<?php

namespace BitApps\Social\HTTP\Requests;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;

class ProxyRequest extends Request
{
    public function rules()
    {
        return [
            'url'         => ['required', 'string', 'url', 'sanitize:url'],
            'method'      => ['required', 'string', 'sanitize:text'],
            'headers'     => ['nullable'],
            'queryParams' => ['nullable'],
            'bodyParams'  => ['nullable'],
        ];
    }
}
