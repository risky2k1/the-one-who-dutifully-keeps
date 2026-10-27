<?php

namespace Botble\Portfolio\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PortfolioSkillGroupRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'badge' => ['nullable', 'string', 'max:50'],
            'accent_color' => ['nullable', 'string', 'max:30'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }
}

