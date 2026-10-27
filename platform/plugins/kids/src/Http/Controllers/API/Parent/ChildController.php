<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\ChildRequest;
use Botble\Kids\Http\Resources\API\ChildResource;
use Botble\Kids\Models\KidsParent;
use Illuminate\Http\Request;

class ChildController extends BaseApiController
{
    public function index(Request $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $children = $parent->children()->get();

        return $this->httpResponse()->setData(ChildResource::collection($children))->toApiResponse();
    }

    public function store(ChildRequest $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $child = $parent->children()->create($request->validated());

        return $this->httpResponse()->setData(new ChildResource($child))->toApiResponse();
    }

    public function update(int|string $child, ChildRequest $request): mixed
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

        $childModel->update($request->validated());

        return $this->httpResponse()->setData(new ChildResource($childModel))->toApiResponse();
    }
}
