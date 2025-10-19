<?php

namespace BitApps\Social\HTTP\Requests;

use BitApps\Social\Deps\BitApps\WPKit\Http\Request\Request;

class SettingsUpdateRequest extends Request
{
    public function rules()
    {
        return [
            'settings' => ['required', 'array'],
        ];
    }
}
