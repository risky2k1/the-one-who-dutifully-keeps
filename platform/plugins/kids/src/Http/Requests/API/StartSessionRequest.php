<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Validator;

class StartSessionRequest extends Request
{
    public function rules(): array
    {
        return [
            'topic_id' => ['nullable', 'integer', 'exists:kids_topics,id'],
            'assignment_id' => ['nullable', 'integer', 'exists:kids_assignments,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('topic_id') && ! $this->filled('assignment_id')) {
                $validator->errors()->add('session', 'Vui lòng cung cấp topic_id hoặc assignment_id.');
            }
        });
    }
}
