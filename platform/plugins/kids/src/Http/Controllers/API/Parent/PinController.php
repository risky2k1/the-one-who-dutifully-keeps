<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\PinRequest;
use Botble\Kids\Models\KidsParent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PinController extends BaseApiController
{
    public function update(PinRequest $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();

        if (! Hash::check($request->input('password'), $parent->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mật khẩu hiện tại không chính xác.'],
            ]);
        }

        $parent->update([
            'pin_hash' => Hash::make($request->input('pin')),
        ]);

        return $this->httpResponse()->setData(null)->toApiResponse();
    }
}
