<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_contact_access_logs', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->char('survey_id', 36);
            $table->char('response_id', 36);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accessed_at');
            $table->char('request_hash', 64)->nullable();
            $table->timestamps();
            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnDelete();
            $table->foreign('response_id')->references('id')->on('survey_responses')->cascadeOnDelete();
            $table->index(['survey_id', 'response_id', 'accessed_at'], 'survey_contact_access_lookup');
        });

        Schema::table('survey_responses', function (Blueprint $table): void {
            $table->index(['survey_id', 'zone', 'submitted_at'], 'survey_responses_zone_filter');
            $table->index(['survey_id', 'age_range', 'submitted_at'], 'survey_responses_age_filter');
        });

        Schema::table('survey_answers', function (Blueprint $table): void {
            $table->index(['question_id', 'value_int', 'response_id'], 'survey_answers_metric_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('survey_answers', function (Blueprint $table): void {
            $table->dropIndex('survey_answers_metric_lookup');
        });
        Schema::table('survey_responses', function (Blueprint $table): void {
            $table->dropIndex('survey_responses_zone_filter');
            $table->dropIndex('survey_responses_age_filter');
        });
        Schema::dropIfExists('survey_contact_access_logs');
    }
};
