<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\AssignmentRequest;
use Botble\Kids\Http\Resources\API\AssignmentResource;
use Botble\Kids\Models\KidsParent;
use Illuminate\Http\Request;

class AssignmentController extends BaseApiController
{
    public function index(Request $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $query = $parent->assignments();

        if ($request->filled('child_id')) {
            $query->where('child_id', $request->input('child_id'));
        }

        $assignments = $query->latest()->get();

        return $this->httpResponse()->setData(AssignmentResource::collection($assignments))->toApiResponse();
    }

    public function store(AssignmentRequest $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $data = $request->validated();

        $data['question_count'] = $data['question_count'] ?? 10;
        $data['difficulty'] = $data['difficulty'] ?? 'adaptive';
        $data['recurrence'] = $data['recurrence'] ?? 'none';

        $assignment = $parent->assignments()->create($data);

        return $this->httpResponse()->setData(new AssignmentResource($assignment))->toApiResponse();
    }

    public function update(int|string $assignment, AssignmentRequest $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $assignmentModel = $parent->assignments()->find($assignment);

        if (! $assignmentModel) {
            return response()->json([
                'error' => true,
                'message' => 'Assignment not found.',
                'data' => null,
            ], 404);
        }

        $assignmentModel->update($request->validated());

        return $this->httpResponse()->setData(new AssignmentResource($assignmentModel))->toApiResponse();
    }

    public function destroy(int|string $assignment, Request $request): mixed
    {
        /** @var KidsParent $parent */
        $parent = $request->user();
        $assignmentModel = $parent->assignments()->find($assignment);

        if (! $assignmentModel) {
            return response()->json([
                'error' => true,
                'message' => 'Assignment not found.',
                'data' => null,
            ], 404);
        }

        $assignmentModel->delete();

        return $this->httpResponse()->setData(null)->toApiResponse();
    }
}
