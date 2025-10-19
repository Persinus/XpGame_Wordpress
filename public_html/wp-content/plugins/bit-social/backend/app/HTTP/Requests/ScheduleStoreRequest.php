<?php

namespace BitApps\Social\HTTP\Requests;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;

class ScheduleStoreRequest extends Request
{
    public function rules()
    {
        return [
            'settings'     => ['required', 'array'],
            'post_filters' => ['required', 'array'],
            'accounts'     => ['required', 'array'],
            'templates'    => ['required', 'array'],
        ];
    }
}
