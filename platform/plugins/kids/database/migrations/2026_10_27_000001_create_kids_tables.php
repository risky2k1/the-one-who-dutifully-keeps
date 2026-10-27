<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kids_parents', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('pin_hash')->nullable();
            $table->string('status', 60)->default('active');
            $table->timestamps();
        });

        Schema::create('kids_children', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->constrained('kids_parents')->cascadeOnDelete();
            $table->string('name');
            $table->string('avatar');
            $table->tinyInteger('grade')->unsigned();
            $table->string('textbook')->default('ket_noi');
            $table->json('disabled_topic_ids')->nullable();
            $table->boolean('assigned_first')->default(false);
            $table->unsignedInteger('streak_current')->default(0);
            $table->unsignedInteger('streak_best')->default(0);
            $table->date('last_practiced_on')->nullable();
            $table->timestamps();
        });

        Schema::create('kids_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('kids_topics')->cascadeOnDelete();
            $table->tinyInteger('grade')->unsigned();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('generator')->nullable();
            $table->json('generator_config')->nullable();
            $table->string('status', 60)->default('draft');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_word_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->constrained('kids_topics')->cascadeOnDelete();
            $table->text('body');
            $table->string('op', 10);
            $table->string('unit', 50);
            $table->string('status', 60)->default('approved');
            $table->timestamps();
        });

        Schema::create('kids_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->constrained('kids_parents')->cascadeOnDelete();
            $table->foreignId('child_id')->constrained('kids_children')->cascadeOnDelete();
            $table->string('title');
            $table->json('topic_ids');
            $table->unsignedInteger('question_count')->default(10);
            $table->string('difficulty')->default('adaptive');
            $table->dateTime('due_at')->nullable();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->string('recurrence')->default('none');
            $table->timestamps();
        });

        Schema::create('kids_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_id')->constrained('kids_children')->cascadeOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('kids_assignments')->nullOnDelete();
            $table->bigInteger('seed');
            $table->string('status', 60)->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('first_try_correct')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_session_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('session_id')->constrained('kids_sessions')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->foreignId('topic_id')->constrained('kids_topics')->cascadeOnDelete();
            $table->unsignedBigInteger('review_item_id')->nullable();
            $table->string('type');
            $table->tinyInteger('level')->default(1);
            $table->json('prompt');
            $table->json('answer');
            $table->json('hints');
            $table->json('solution');
            $table->json('params');
            $table->tinyInteger('attempts')->default(0);
            $table->string('state', 60)->default('open');
            $table->boolean('first_try_correct')->nullable();
            $table->json('given_answers')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'position']);
        });

        Schema::create('kids_skill_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_id')->constrained('kids_children')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('kids_topics')->cascadeOnDelete();
            $table->tinyInteger('level')->default(1);
            $table->dateTime('level_changed_at');
            $table->timestamps();

            $table->unique(['child_id', 'topic_id']);
        });

        Schema::create('kids_review_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_id')->constrained('kids_children')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('kids_topics')->cascadeOnDelete();
            $table->json('question');
            $table->tinyInteger('stage')->default(1);
            $table->dateTime('due_at');
            $table->timestamps();

            $table->index(['child_id', 'due_at']);
        });

        Schema::create('kids_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('child_id')->constrained('kids_children')->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('kids_sessions')->nullOnDelete();
            $table->string('sticker_key');
            $table->timestamps();
        });

        Schema::create('kids_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained('kids_session_questions')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('kids_parents')->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->string('status', 60)->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
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
};
