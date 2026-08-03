<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->char('survey_id', 36);
            $table->char('comment_id', 36)->nullable();
            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnDelete();
            $table->foreign('comment_id')->references('id')->on('comments')->nullOnDelete();
            $table->char('ip_hash', 64);
            $table->char('user_agent_hash', 64)->nullable();
            $table->text('respondent_contact')->nullable();
            $table->string('zone', 32)->nullable();
            $table->string('age_range', 16)->nullable();
            $table->unsignedInteger('completion_ms')->nullable();
            $table->timestamp('submitted_at');
            $table->date('response_date');
            $table->timestamps();
            $table->unique(['survey_id', 'ip_hash', 'response_date'], 'survey_responses_dedupe_unique');
            $table->index(['survey_id', 'submitted_at']);
        });

        Schema::create('survey_answers', function (Blueprint $table): void {
            $table->id();
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('response_id', 36);
            $table->char('question_id', 36);
            $table->foreign('response_id')->references('id')->on('survey_responses')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('survey_questions')->cascadeOnDelete();
            $table->smallInteger('value_int')->nullable();
            $table->text('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();
            $table->unique(['response_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
    }
};
