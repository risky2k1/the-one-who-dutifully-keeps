<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Kids\Models\KidsChild;
use Botble\Kids\Models\KidsTopic;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssignmentRequest extends Request
{
    public function rules(): array
    {
        $isCreate = $this->isMethod('POST');
        $parentId = $this->user()?->getKey();

        return [
            'child_id' => [
                $isCreate ? 'required' : 'sometimes',
                'integer',
                Rule::exists('kids_children', 'id')->where('parent_id', $parentId),
            ],
            'title' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:255'],
            'topic_ids' => [$isCreate ? 'required' : 'sometimes', 'array', 'min:1'],
            'topic_ids.*' => ['integer', 'exists:kids_topics,id'],
            'question_count' => ['nullable', 'integer', 'between:5,30'],
            'difficulty' => ['nullable', 'string', Rule::in(['adaptive', 'easy', 'medium', 'hard'])],
            'due_at' => ['nullable', 'date'],
            'time_limit_seconds' => ['nullable', 'integer', 'between:60,3600'],
            'recurrence' => ['nullable', 'string', Rule::in(['none', 'daily'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $childId = $this->input('child_id') ?? $this->route('assignment')?->child_id;
            $child = $childId ? KidsChild::query()->find($childId) : null;
            $topicIds = $this->input('topic_ids');

            if ($child && is_array($topicIds) && ! empty($topicIds)) {
                $topics = KidsTopic::query()->whereIn('id', $topicIds)->get();

                foreach ($topics as $topic) {
                    if ((string) $topic->status?->getValue() !== TopicStatusEnum::APPROVED && (string) $topic->status !== TopicStatusEnum::APPROVED) {
                        $validator->errors()->add('topic_ids', "Chủ đề {$topic->name} chưa được phê duyệt.");
                    }

                    if (! $topic->isLeaf()) {
                        $validator->errors()->add('topic_ids', "Chủ đề {$topic->name} không phải chủ đề lá để luyện tập.");
                    }

                    if ($topic->grade !== $child->grade) {
                        $validator->errors()->add('topic_ids', "Chủ đề {$topic->name} (Lớp {$topic->grade}) không thuộc lớp của bé (Lớp {$child->grade}).");
                    }
                }
            }
        });
    }
}
