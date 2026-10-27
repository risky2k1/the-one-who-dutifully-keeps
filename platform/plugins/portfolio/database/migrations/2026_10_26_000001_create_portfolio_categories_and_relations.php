<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('portfolio_categories')) {
            Schema::create('portfolio_categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->unsignedBigInteger('parent_id')->nullable()->default(0);
                $table->string('badge', 50)->nullable();
                $table->string('accent_color', 30)->default('#5ec2b7');
                $table->text('description')->nullable();
                $table->integer('order')->default(0);
                $table->string('status', 60)->default('published');
                $table->timestamps();

                $table->index('parent_id');
                $table->index(['status', 'order']);
            });
        }

        if (! Schema::hasTable('portfolio_project_categories')) {
            Schema::create('portfolio_project_categories', function (Blueprint $table): void {
                $table->foreignId('project_id')->constrained('portfolio_projects')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('portfolio_categories')->cascadeOnDelete();
                $table->primary(['project_id', 'category_id']);
            });
        }

        if (Schema::hasTable('portfolio_skills') && ! Schema::hasColumn('portfolio_skills', 'category_id')) {
            Schema::table('portfolio_skills', function (Blueprint $table): void {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('skill_group_id')
                    ->constrained('portfolio_categories')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('portfolio_skills') && Schema::hasColumn('portfolio_skills', 'category_id')) {
            Schema::table('portfolio_skills', function (Blueprint $table): void {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        Schema::dropIfExists('portfolio_project_categories');
        Schema::dropIfExists('portfolio_categories');
    }
};
