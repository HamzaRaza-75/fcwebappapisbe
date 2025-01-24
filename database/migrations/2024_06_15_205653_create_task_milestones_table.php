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
        Schema::create('task_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();

            $table->bigInteger('assigned_to');
            $table->bigInteger('assigned_by');
            $table->string('task_milestone_name')->nullable();
            $table->longText('task_milestone_description')->nullable();
            $table->string('task_milestone_file')->nullable();
            $table->enum('status', ['complete', 'incomplete'])->default('incomplete');
            $table->string('worth')->nullable();
            $table->string('word_count')->nullable();
            $table->dateTime('deadline_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            $table->dateTime('seen_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_milestones');
    }
};
