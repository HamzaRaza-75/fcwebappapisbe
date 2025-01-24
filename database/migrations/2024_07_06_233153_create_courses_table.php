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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('creater_id');
            $table->string('course_name')->unique();
            $table->text('course_description')->nullable();
            $table->text('course_image')->nullable();
            $table->enum('status' , ['approved' , 'pending' , 'cancelled'])->default('pending');

            $table->string('platform')->nullable();
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->string('duration_in_days')->nullable();
            $table->string('platform_url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
