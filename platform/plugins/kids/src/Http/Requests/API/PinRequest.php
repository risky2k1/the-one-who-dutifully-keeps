<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class PinRequest extends Request
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'pin' => ['required', 'string', 'regex:/^\d{4,6}$/'],
        ];
    }
}
