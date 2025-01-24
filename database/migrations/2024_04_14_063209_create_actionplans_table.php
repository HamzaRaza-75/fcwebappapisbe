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
        Schema::create('actionplans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');

            $table->bigInteger('submited_by');
            $table->string('task_file')->nullable();
            $table->string('word_count')->nullable();
            $table->string('worth')->nullable();
            $table->boolean('revision')->default(0);

            $table->dateTime('action_plan_starting_datetime')->nullable();
            $table->dateTime('action_plan_submition_datetime')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actionplans');
    }
};
