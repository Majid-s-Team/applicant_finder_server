<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('website')->nullable();
            $table->date('founded_date')->nullable();
            $table->string('sector')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->enum('public_private_profile', ['public', 'private'])->default('public');
            $table->string('profile_url')->nullable()->unique();
            $table->string('job_title')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
