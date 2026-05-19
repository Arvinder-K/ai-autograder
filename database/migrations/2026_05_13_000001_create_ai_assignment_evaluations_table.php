<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_assignment_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('student_name');
            $table->string('assignment_name');
            $table->string('prompt_file');
            $table->string('zip_file');
            $table->text('evaluation_report');
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_assignment_evaluations');
    }
};
