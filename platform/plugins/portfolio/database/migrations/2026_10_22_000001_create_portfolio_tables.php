<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('portfolio_projects')) {
            Schema::create('portfolio_projects', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->string('category', 100)->default('Backend API');
                $table->text('short_summary')->nullable();
                $table->longText('overview')->nullable();
                $table->string('role', 255)->nullable();
                $table->string('timeline', 100)->nullable();
                $table->string('accent_color', 30)->default('#5ec2b7');
                $table->string('secondary_color', 30)->default('#a8d8ea');
                $table->string('architecture_diagram_type', 50)->default('gateway_mesh');
                $table->longText('challenges')->nullable();
                $table->longText('outcomes')->nullable();
                $table->longText('tech_stack')->nullable();
                $table->longText('metrics')->nullable();
                $table->longText('links')->nullable();
                $table->integer('order')->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('portfolio_skill_groups')) {
            Schema::create('portfolio_skill_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 100)->unique();
                $table->string('badge', 50)->nullable();
                $table->string('accent_color', 30)->default('#5ec2b7');
                $table->text('summary')->nullable();
                $table->integer('order')->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('portfolio_skills')) {
            Schema::create('portfolio_skills', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('skill_group_id')->constrained('portfolio_skill_groups')->cascadeOnDelete();
                $table->string('name', 255);
                $table->string('level', 100)->default('Production');
                $table->text('context')->nullable();
                $table->boolean('highlight')->default(false);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('portfolio_experiences')) {
            Schema::create('portfolio_experiences', function (Blueprint $table): void {
                $table->id();
                $table->string('slug', 100)->unique();
                $table->string('role', 255);
                $table->string('organization', 255);
                $table->string('organization_note', 255)->nullable();
                $table->string('period', 100);
                $table->string('location', 255)->nullable();
                $table->text('summary')->nullable();
                $table->longText('highlights')->nullable();
                $table->longText('technologies')->nullable();
                $table->integer('order')->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_skills');
        Schema::dropIfExists('portfolio_skill_groups');
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('portfolio_experiences');
    }
};

