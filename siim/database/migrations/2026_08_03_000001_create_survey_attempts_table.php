<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_attempts', function (Blueprint $table): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36)->primary();
            $table->char('survey_id', 36);
            $table->char('response_id', 36)->nullable()->unique();
            $table->foreign('survey_id')->references('id')->on('surveys')->cascadeOnDelete();
            $table->foreign('response_id')->references('id')->on('survey_responses')->cascadeOnDelete();
            $table->timestamp('started_at', precision: 6);
            $table->timestamp('completed_at', precision: 6)->nullable();
            $table->timestamps();
            $table->index(['survey_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_attempts');
    }
};
