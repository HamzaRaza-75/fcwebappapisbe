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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();

            $table->enum('working_domain' , ['office' , 'assignmentbase'])->default('office');
            $table->bigInteger('user_id');
            $table->string('phone_no')->nullable();
            $table->string('gurdian_name')->nullable();
            $table->string('gurdian_phone_no')->nullable();
            $table->string('CNIC_image')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('dateofbirth')->nullable();
            $table->enum('gender' , ['male' , 'female'])->nullable();
            $table->string('current_address')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
