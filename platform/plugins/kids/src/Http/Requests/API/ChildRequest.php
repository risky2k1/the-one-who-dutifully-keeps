<?php

namespace Botble\Kids\Http\Requests\API;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class ChildRequest extends Request
{
    public function rules(): array
    {
        $avatars = config('plugins.kids.general.avatars', ['fox', 'cat', 'panda', 'bunny', 'owl', 'dino', 'bear', 'penguin']);
        $isCreate = $this->isMethod('POST');

        return [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string', 'max:120'],
            'avatar' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::in($avatars)],
            'grade' => [$isCreate ? 'required' : 'sometimes', 'integer', 'between:1,5'],
            'textbook' => [$isCreate ? 'required' : 'sometimes', 'string', Rule::in(['ket_noi', 'chan_troi', 'canh_dieu'])],
            'disabled_topic_ids' => ['nullable', 'array'],
            'disabled_topic_ids.*' => ['integer', 'exists:kids_topics,id'],
            'assigned_first' => ['nullable', 'boolean'],
        ];
    }
}
