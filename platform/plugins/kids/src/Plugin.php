<?php

namespace Botble\Kids;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('kids_reports');
        Schema::dropIfExists('kids_rewards');
        Schema::dropIfExists('kids_review_items');
        Schema::dropIfExists('kids_skill_levels');
        Schema::dropIfExists('kids_session_questions');
        Schema::dropIfExists('kids_sessions');
        Schema::dropIfExists('kids_assignments');
        Schema::dropIfExists('kids_word_templates');
        Schema::dropIfExists('kids_topics');
        Schema::dropIfExists('kids_children');
        Schema::dropIfExists('kids_parents');
    }
}
