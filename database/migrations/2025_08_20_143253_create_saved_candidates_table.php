<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employer_id'); // Employer who saves
            $table->unsignedBigInteger('candidate_id'); // Candidate who is saved
            $table->unsignedBigInteger('job_id')->nullable(); // Optionally link to job
            $table->timestamps();

            // Foreign keys
            $table->foreign('employer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('candidate_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');

            // Unique constraint to prevent duplicate saves
            $table->unique(['employer_id', 'candidate_id', 'job_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_candidates');
    }
};
