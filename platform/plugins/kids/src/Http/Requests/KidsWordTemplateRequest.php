<?php

namespace Botble\Kids\Http\Requests;

use Botble\Kids\Enums\TopicStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class KidsWordTemplateRequest extends Request
{
    public function rules(): array
    {
        return [
            'topic_id' => ['required', 'integer'],
            'op' => ['required', 'string', Rule::in(['+', '-', '×', '÷', '*', '/'])],
            'body' => ['required', 'string'],
            'unit' => ['required', 'string', 'max:50'],
            'status' => [Rule::in(TopicStatusEnum::values())],
        ];
    }
}
