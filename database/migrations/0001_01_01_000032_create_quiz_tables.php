<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_section_id')->constrained()->cascadeOnDelete();
            $table->string('question_text');
            $table->text('help_text')->nullable();
            $table->enum('question_type', [
                'single_choice',
                'multi_choice',
                'text',
                'textarea',
                'number',
                'yes_no',
                'scale',
                'dropdown',
                'multi_dropdown',
            ]);
            $table->json('options')->nullable(); // For choice-based questions
            $table->json('conditional_on')->nullable(); // Show only if certain answers given
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('quiz_section_id');
        });

        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_value')->nullable();
            $table->json('answer_options')->nullable(); // For multi-select answers
            $table->timestamps();

            $table->unique(['story_id', 'quiz_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quiz_sections');
    }
};
