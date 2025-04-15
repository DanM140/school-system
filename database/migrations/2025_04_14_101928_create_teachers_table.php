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
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Foreign keys for multi-tenant structure
            $table->uuid('school_id');
           // $table->uuid('branch_id')->nullable();
           // $table->uuid('department_id')->nullable();
        
            // Personal Info
            $table->string('employee_number')->unique(); // Like staff ID
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
        
            // Professional Info
            $table->string('qualification')->nullable();   // e.g., B.Ed, M.Sc
            $table->string('designation')->nullable();     // e.g., Senior Teacher
            $table->string('subject_specialization')->nullable(); // e.g., Math
            $table->date('date_joined')->nullable();
        
            // Other
            $table->string('photo')->nullable(); // Profile picture
            $table->text('address')->nullable();
            $table->string('status')->default('active'); // active, on_leave, resigned, etc.
        
            $table->timestamps();
            $table->softDeletes(); // Enable soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
