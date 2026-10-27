<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Validator;

class AnswerRequest extends Request
{
    public function rules(): array
    {
        return [
            'value' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->has('value') && trim((string) $this->input('value')) === '') {
                $validator->errors()->add('value', 'Đáp án không được để trống.');
            }
        });
    }
}
