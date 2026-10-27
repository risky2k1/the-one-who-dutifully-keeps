<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;

class UnlockRequest extends Request
{
    public function rules(): array
    {
        return [
            'pin' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
        ];
    }
}
