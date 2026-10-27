<?php

namespace Botble\Portfolio\Http\Requests;

use Botble\Support\Http\Requests\Request;

class PortfolioSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'portfolio_persona_name' => ['nullable', 'string', 'max:255'],
            'portfolio_persona_role' => ['nullable', 'string', 'max:255'],
            'portfolio_persona_specialization' => ['nullable', 'string', 'max:500'],
            'portfolio_persona_location' => ['nullable', 'string', 'max:255'],
            'portfolio_persona_availability_badge' => ['nullable', 'string', 'max:255'],
            'portfolio_intro_headline' => ['nullable', 'string', 'max:500'],
            'portfolio_intro_lead' => ['nullable', 'string', 'max:2000'],
            'portfolio_demonstration_notice' => ['nullable', 'string', 'max:1000'],
            'portfolio_about_headline' => ['nullable', 'string', 'max:500'],
            'portfolio_contact_headline' => ['nullable', 'string', 'max:255'],
            'portfolio_contact_subtitle' => ['nullable', 'string', 'max:1000'],
            'portfolio_contact_availability_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

