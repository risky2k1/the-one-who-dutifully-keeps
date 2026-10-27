<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Resources\API\TopicResource;
use Botble\Kids\Models\KidsTopic;
use Illuminate\Http\Request;

class TopicController extends BaseApiController
{
    public function index(Request $request): mixed
    {
        $query = KidsTopic::query()->approved();

        if ($request->filled('grade')) {
            $query->where('grade', (int) $request->input('grade'));
        }

        $topics = $query->orderBy('order')->get();

        return $this->httpResponse()->setData(TopicResource::collection($topics))->toApiResponse();
    }
}
