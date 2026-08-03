<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->string('slug', 64)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'opens_at', 'closes_at']);
        });

        Schema::create('survey_questions', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->char('survey_id', 36);
            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->enum('type', ['scale_1_5', 'single_choice', 'multi_choice', 'open_text', 'nps']);
            $table->string('label', 300);
            $table->string('help_text', 300)->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('max_selections')->nullable();
            $table->unsignedSmallInteger('max_length')->nullable();
            $table->string('topic_slug', 64)->nullable();
            $table->timestamps();
            $table->unique(['survey_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('surveys');
    }
};
