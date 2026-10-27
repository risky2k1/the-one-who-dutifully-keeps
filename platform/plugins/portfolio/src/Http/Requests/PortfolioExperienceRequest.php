<?php

namespace Botble\Portfolio\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PortfolioExperienceRequest extends Request
{
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('portfolio_experiences', 'slug')->ignore($this->route('experience'))],
            'organization' => ['required', 'string', 'max:255'],
            'organization_note' => ['nullable', 'string', 'max:255'],
            'period' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }
}

