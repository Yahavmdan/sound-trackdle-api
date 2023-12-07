<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('full_name');
            $table->unsignedInteger('grade');
            $table->timestamps();
        });

        Schema::create('period_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->onDelete('restrict');
            $table->foreignId('student_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_student');
        Schema::dropIfExists('students');
    }
};
