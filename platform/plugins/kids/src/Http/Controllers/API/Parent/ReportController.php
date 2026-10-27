<?php

namespace Botble\Kids\Http\Controllers\API\Parent;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Kids\Enums\ReportStatusEnum;
use Botble\Kids\Models\KidsParent;
use Botble\Kids\Models\KidsReport;
use Botble\Kids\Models\KidsSessionQuestion;
use Illuminate\Http\Request;

class ReportController extends BaseApiController
{
    public function report(int|string $question, Request $request): mixed
    {
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        /** @var KidsParent $parent */
        $parent = $request->user();

        $questionModel = KidsSessionQuestion::query()
            ->whereHas('session.child', fn ($q) => $q->where('parent_id', $parent->getKey()))
            ->find($question);

        if (! $questionModel) {
            return response()->json([
                'error' => true,
                'message' => 'Question not found.',
                'data' => null,
            ], 404);
        }

        KidsReport::query()->create([
            'question_id' => $questionModel->getKey(),
            'parent_id' => $parent->getKey(),
            'note' => $request->input('note'),
            'status' => ReportStatusEnum::OPEN,
        ]);

        return $this->httpResponse()->setData(null)->toApiResponse();
    }
}
