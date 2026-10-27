<?php

namespace Botble\Kids\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\LoginRequest;
use Botble\Kids\Http\Resources\API\ParentResource;
use Botble\Kids\Models\KidsParent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        /** @var KidsParent|null $parent */
        $parent = KidsParent::query()->where('username', $request->input('username'))->first();

        if (
            ! $parent ||
            ! Hash::check($request->input('password'), $parent->password) ||
            $parent->status !== 'active'
        ) {
            throw ValidationException::withMessages([
                'username' => ['Tên đăng nhập hoặc mật khẩu không chính xác hoặc tài khoản đã bị vô hiệu hóa.'],
            ]);
        }

        $parentTtl = (int) config('plugins.kids.general.token_ttl_minutes.parent', 720);
        $deviceTtl = (int) config('plugins.kids.general.token_ttl_minutes.device', 259200);

        $parentToken = $parent->createToken('kids-parent', ['parent'], now()->addMinutes($parentTtl));
        $deviceName = $request->input('device_name');
        $deviceToken = $parent->createToken("kids-device:{$deviceName}", ['device'], now()->addMinutes($deviceTtl));

        return $this->httpResponse()->setData([
            'parent_token' => $parentToken->plainTextToken,
            'device_token' => $deviceToken->plainTextToken,
            'parent' => new ParentResource($parent),
        ])->toApiResponse();
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->httpResponse()->setData(null)->toApiResponse();
    }
}
