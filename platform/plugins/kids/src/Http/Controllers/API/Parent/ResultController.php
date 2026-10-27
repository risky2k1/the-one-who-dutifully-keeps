<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Resources\API\AssignmentResultResource;
use Botble\Kids\Http\Resources\API\SessionReviewResource;
use Botble\Kids\Http\Resources\API\WeakSkillResource;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsSession;
use Botble\Kids\Services\ResultsService;
use Illuminate\Http\Request;

class ResultController extends BaseApiController
{
    public function index(int|string $child, Request $request, ResultsService $service): mixed
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

        $assignmentId = $request->query('assignment_id') ? (int) $request->query('assignment_id') : null;
        $results = $service->assignmentResults($childModel, $assignmentId);

        return $this->httpResponse()->setData(AssignmentResultResource::collection($results))->toApiResponse();
    }

    public function weakSkills(int|string $child, Request $request, ResultsService $service): mixed
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

        $weak = $service->weakSkills($childModel);

        return $this->httpResponse()->setData(WeakSkillResource::collection($weak))->toApiResponse();
    }

    public function sessionReview(int|string $session, Request $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $sessionModel = KidsSession::query()
            ->whereHas('child', fn ($q) => $q->where('parent_id', $parent->getKey()))
            ->with(['questions.topic'])
            ->find($session);

        if (! $sessionModel) {
            return response()->json([
                'error' => true,
                'message' => 'Session not found.',
                'data' => null,
            ], 404);
        }

        return $this->httpResponse()->setData(new SessionReviewResource($sessionModel))->toApiResponse();
    }
}
