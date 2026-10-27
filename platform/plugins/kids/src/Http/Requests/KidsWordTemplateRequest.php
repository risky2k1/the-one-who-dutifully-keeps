<?php

namespace Botble\Kids\Http\Requests;

use Botble\Kids\Enums\TemplateStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KidsWordTemplateRequest extends Request
{
    public function rules(): array
    {
        return [
            'topic_id' => ['nullable', 'integer'],
            'grade' => ['required', 'integer', 'between:1,5'],
            'operation' => ['required', 'string', Rule::in(['add', 'sub', 'mul', 'div'])],
            'template' => ['required', 'string'],
            'var_ranges' => ['required'],
            'result_variable' => ['required', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(TemplateStatusEnum::values())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $raw = $this->input('var_ranges');
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    $validator->errors()->add('var_ranges', 'The var_ranges field must be a valid JSON object.');
                }
            } elseif (! is_array($raw)) {
                $validator->errors()->add('var_ranges', 'The var_ranges field must be a valid JSON array/object.');
            }
        });
    }
}
