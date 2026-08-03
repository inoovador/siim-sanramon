<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->string('text', 5000);
            $table->enum('channel', ['meta_facebook', 'meta_instagram', 'web_form', 'csv_upload', 'public_chatbot', 'web_survey']);
            $table->string('source');
            $table->string('external_id', 191)->nullable();
            $table->string('author_alias', 80)->nullable();
            $table->text('author_contact')->nullable();
            $table->char('language', 2)->default('es');
            $table->timestamp('captured_at');
            $table->timestamp('redacted_at')->nullable();
            $table->timestamps();
            $table->unique(['channel', 'external_id']);
            $table->index(['channel', 'captured_at']);
        });

        Schema::create('topics', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->string('slug', 64)->unique();
            $table->string('label', 120);
            $table->string('description', 300)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sentiment_scores', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('comment_id', 36)->primary();
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->enum('polarity', ['positive', 'neutral', 'negative']);
            $table->decimal('score', 4, 3);
            $table->decimal('confidence', 4, 3);
            $table->string('reason', 500)->nullable();
            $table->timestamp('analyzed_at');
        });

        Schema::create('topic_assignments', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('comment_id', 36);
            $table->char('topic_id', 36);
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
            $table->decimal('confidence', 4, 3);
            $table->timestamp('assigned_at');
            $table->primary(['comment_id', 'topic_id']);
        });

        Schema::create('analysis_runs', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->char('comment_id', 36);
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->string('llm_provider', 40);
            $table->string('llm_model', 80);
            $table->enum('status', ['pending', 'success', 'failed', 'fallback']);
            $table->unsignedInteger('tokens_input')->nullable();
            $table->unsignedInteger('tokens_output')->nullable();
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->string('error_message', 500)->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->index(['comment_id', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_runs');
        Schema::dropIfExists('topic_assignments');
        Schema::dropIfExists('sentiment_scores');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('comments');
    }
};
