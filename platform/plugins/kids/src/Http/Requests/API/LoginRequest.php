<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class LoginRequest extends Request
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string'],
        ];
    }
}
