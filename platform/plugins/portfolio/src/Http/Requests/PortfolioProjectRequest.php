<?php

namespace Botble\Portfolio\Http\Requests;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class PortfolioProjectRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('portfolio_categories', 'id')],
            'category' => ['nullable', 'string', 'max:100'],
            'short_summary' => ['nullable', 'string', 'max:1000'],
            'overview' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:255'],
            'timeline' => ['nullable', 'string', 'max:100'],
            'accent_color' => ['nullable', 'string', 'max:30'],
            'secondary_color' => ['nullable', 'string', 'max:30'],
            'architecture_diagram_type' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(BaseStatusEnum::values())],
        ];
    }
}
