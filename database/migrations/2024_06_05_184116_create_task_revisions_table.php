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
        Schema::create('task_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('action_plan_id');
            $table->string('revision_title')->nullable();
            $table->longText('revision_description')->nullable();
            $table->string('revision_file')->nullable();
            $table->boolean('completed')->default(false);
            $table->dateTime('seen_at')->nullable();
            $table->dateTime('deadline_date')->nullable();


            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_revisions');
    }
};
