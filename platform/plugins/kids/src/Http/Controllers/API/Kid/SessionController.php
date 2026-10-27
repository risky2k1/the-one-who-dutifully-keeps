<?php

namespace Botble\Kids\Http\Controllers\API\Kid;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Http\Requests\API\AnswerRequest;
use Botble\Kids\Http\Requests\API\StartSessionRequest;
use Botble\Kids\Http\Resources\API\PracticeSessionResource;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsTopic;
use Botble\Kids\Services\AnsweringService;
use Botble\Kids\Services\PracticeSessionService;
use Botble\Kids\Services\SessionCompletionService;
use Illuminate\Http\Request;

class SessionController extends BaseApiController
{
    public function start(StartSessionRequest $request, PracticeSessionService $service): mixed
    {
        /** @var KidsChild $child */
        $child = $request->user();

        $topic = $request->filled('topic_id')
            ? KidsTopic::query()->find($request->input('topic_id'))
            : null;

        $assignment = null;
        if ($request->filled('assignment_id')) {
            $assignment = $child->assignments()->find($request->input('assignment_id'));
            if (! $assignment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Assignment not found.',
                    'data' => null,
                ], 404);
            }
        }

        $session = $service->start($child, $topic, $assignment);

        return $this->httpResponse()
            ->setData(new PracticeSessionResource($session))
            ->setCode(201)
            ->toApiResponse();
    }

    public function answer(
        int|string $session,
        int|string $question,
        AnswerRequest $request,
        AnsweringService $service
    ): mixed {
        /** @var KidsChild $child */
        $child = $request->user();

        $sessionModel = $child->sessions()->find($session);
        if (! $sessionModel) {
            return response()->json([
                'error' => true,
                'message' => 'Session not found.',
                'data' => null,
            ], 404);
        }

        $questionModel = $sessionModel->questions()->find($question);
        if (! $questionModel) {
            return response()->json([
                'error' => true,
                'message' => 'Question not found.',
                'data' => null,
            ], 404);
        }

        $result = $service->answer($questionModel, $request->input('value'));

        return $this->httpResponse()->setData($result)->toApiResponse();
    }

    public function complete(
        int|string $session,
        SessionCompletionService $service,
        Request $request
    ): mixed {
        /** @var KidsChild $child */
        $child = $request->user();

        $sessionModel = $child->sessions()->find($session);
        if (! $sessionModel) {
            return response()->json([
                'error' => true,
                'message' => 'Session not found.',
                'data' => null,
            ], 404);
        }

        $summary = $service->complete($sessionModel);

        return $this->httpResponse()->setData($summary)->toApiResponse();
    }
}
