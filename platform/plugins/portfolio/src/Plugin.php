<?php

namespace Botble\Portfolio;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('portfolio_skills');
        Schema::dropIfExists('portfolio_skill_groups');
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('portfolio_experiences');

        Setting::delete([
            'portfolio_persona_name',
            'portfolio_persona_role',
            'portfolio_persona_specialization',
            'portfolio_persona_location',
            'portfolio_persona_availability_badge',
            'portfolio_intro_headline',
            'portfolio_intro_lead',
            'portfolio_intro_highlights',
            'portfolio_demonstration_notice',
            'portfolio_is_demonstration_content',
            'portfolio_about_headline',
            'portfolio_about_narrative',
            'portfolio_about_pillars',
            'portfolio_contact_headline',
            'portfolio_contact_subtitle',
            'portfolio_contact_availability_note',
            'portfolio_contact_channels',
            'portfolio_destinations',
        ]);
    }
}

