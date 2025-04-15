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
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
    // Foreign Keys
    $table->uuid('school_id');       // Multi-school support
   // $table->uuid('branch_id')->nullable();       // School branch
   // $table->uuid('department_id')->nullable();   // Science, Arts, etc.
   // $table->uuid('classroom_id')->nullable();    // A specific class/section
    
    // Core Info
    $table->string('student_number')->unique(); // Like a roll number / official ID
    $table->string('first_name');
    $table->string('last_name');
    $table->string('gender');
    $table->date('date_of_birth')->nullable();
    $table->string('email')->nullable()->unique();
    $table->string('phone')->nullable();

    // Parental / Guardian Info (optional)
    $table->string('guardian_name')->nullable();
    $table->string('guardian_phone')->nullable();
    $table->string('guardian_email')->nullable();

    // Additional
    $table->string('photo')->nullable(); // Path to profile photo
    $table->text('address')->nullable();
    $table->string('status')->default('active'); // active, graduated, transferred, etc.

    $table->timestamps();
    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
