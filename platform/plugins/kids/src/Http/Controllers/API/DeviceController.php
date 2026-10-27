<?php

namespace Botble\Kids\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\UnlockRequest;
use Botble\Kids\Http\Resources\API\ChildCardResource;
use Botble\Kids\Models\KidsParent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DeviceController extends BaseApiController
{
    public function unlock(UnlockRequest $request): JsonResponse
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $pin = $request->input('pin');
        $password = $request->input('password');

        if ($pin !== null) {
            if (empty($parent->pin_hash)) {
                throw ValidationException::withMessages([
                    'pin' => ['Mã PIN chưa được thiết lập, vui lòng sử dụng mật khẩu.'],
                ]);
            }

            if (! Hash::check($pin, $parent->pin_hash)) {
                throw ValidationException::withMessages([
                    'pin' => ['Mã PIN không chính xác.'],
                ]);
            }
        } elseif ($password !== null) {
            if (! Hash::check($password, $parent->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Mật khẩu không chính xác.'],
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'pin' => ['Vui lòng cung cấp mã PIN hoặc mật khẩu để mở khóa.'],
            ]);
        }

        $parentTtl = (int) config('plugins.kids.general.token_ttl_minutes.parent', 720);
        $parentToken = $parent->createToken('kids-parent', ['parent'], now()->addMinutes($parentTtl));

        return $this->httpResponse()->setData([
            'parent_token' => $parentToken->plainTextToken,
        ])->toApiResponse();
    }

    public function children(Request $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $children = $parent->children()->get();

        return $this->httpResponse()->setData(ChildCardResource::collection($children))->toApiResponse();
    }

    public function startKidSession(int|string $child, Request $request): JsonResponse
    {
        /** @var KidsParent $parent */
        $parent = $request->user();

        $childModel = $parent->children()->find($child);
        if (! $childModel) {
            return response()->json([
                'error' => true,
                'message' => 'Child not found.',
                'data' => null,
            ], 404);
        }

        $kidTtl = (int) config('plugins.kids.general.token_ttl_minutes.kid', 720);
        $kidToken = $childModel->createToken('kids-kid', ['kid'], now()->addMinutes($kidTtl));

        return $this->httpResponse()->setData([
            'kid_token' => $kidToken->plainTextToken,
            'child' => new ChildCardResource($childModel),
        ])->toApiResponse();
    }
}
