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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_num')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('team_id');

            $table->string('task_name')->nullable();
            $table->longText('task_description')->nullable();
            $table->string('task_file')->nullable();
            $table->enum('status' , ['completed' , 'incomplete' , 'cancelled'])->default('incomplete');
            $table->enum('account' , ['financial' , 'non-financial'])->default('financial');

            $table->string('estimated_budjet')->nullable();
            $table->string('word_count')->nullable();

            $table->dateTime('starting_date')->nullable();
            $table->dateTime('deadline_date')->nullable();
            $table->dateTime('completed_date')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
