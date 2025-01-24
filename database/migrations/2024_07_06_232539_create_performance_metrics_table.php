<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('evaluated_by');
            $table->unsignedBigInteger('evaluated_to');
            $table->decimal('punctuality', 5, 2)->nullable();
            $table->decimal('response_time', 5, 2)->nullable();
            $table->decimal('in_and_out', 5, 2)->nullable();
            $table->decimal('team_spirit', 5, 2)->nullable();
            $table->decimal('attitude_behavior', 5, 2)->nullable();
            $table->decimal('cancellations_quiz_exam_performance', 5, 2)->nullable();
            $table->decimal('formatting_referencing', 5, 2)->nullable();
            $table->decimal('language_grip_it', 5, 2)->nullable();
            $table->decimal('content_flow_quality', 5, 2)->nullable();
            $table->decimal('logics_structure', 5, 2)->nullable();
            $table->decimal('understanding_instructions', 5, 2)->nullable();
            $table->decimal('expertise_technical_subjects', 5, 2)->nullable();
            $table->decimal('advancement_language', 5, 2)->nullable();
            $table->decimal('focused_new_learnings', 5, 2)->nullable();
            $table->decimal('planning_management', 5, 2)->nullable();
            $table->decimal('plagiarism', 5, 2)->nullable();
            $table->decimal('ai_tools_usage', 5, 2)->nullable();
            $table->decimal('flawed_language_it', 5, 2)->nullable();
            $table->decimal('coursework_missed', 5, 2)->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
