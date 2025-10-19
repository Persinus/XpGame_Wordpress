<?php

namespace BitApps\Social\HTTP\Requests;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;

class AuthorizeRequest extends Request
{
    public function rules()
    {
        return [
            'config'  => ['required', 'array'],
            'payload' => ['required', 'array'],
        ];
    }
}
