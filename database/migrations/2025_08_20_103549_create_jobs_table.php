<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::create('jobs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employer_id')->constrained('users')->onDelete('cascade'); // employer user_id
        $table->string('title');
        $table->text('description');
        $table->date('applicant_deadline')->nullable();
        $table->unsignedBigInteger('industry_id')->nullable(); 
        $table->enum('job_type', ['full_time','part_time','contract','internship','freelance']);
        $table->json('required_skills')->nullable(); 
        $table->string('salary_range')->nullable();
        $table->string('career_level')->nullable();
        $table->enum('experience', ['fresher','1-2','3-5','5-10','10+'])->nullable();
        $table->string('qualification')->nullable();
        $table->string('company_name')->nullable();
        $table->string('location')->nullable();
        $table->string('file_attachment')->nullable();
        $table->enum('status', ['draft','active','closed'])->default('active');
        $table->timestamps();
        $table->softDeletes();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
