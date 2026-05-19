<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('system_architecture')->nullable();
            $table->longText('design_details')->nullable();
            $table->longText('presentation_content')->nullable(); // For slide generation
            $table->enum('audience', ['technical', 'business', 'both'])->default('both');
            $table->enum('status', ['draft', 'in_review', 'approved'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('story_id');
        });

        Schema::create('coding_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('technical_design_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedInteger('prompt_sequence')->default(1);
            $table->enum('target_tool', ['claude_ai', 'github_copilot', 'other'])->default('claude_ai');
            $table->longText('prompt_content');
            $table->text('continuation_context')->nullable(); // Context for next micro-prompt
            $table->boolean('is_continuation')->default(false);
            $table->foreignId('parent_prompt_id')->nullable()->constrained('coding_prompts')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['story_id', 'prompt_sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coding_prompts');
        Schema::dropIfExists('technical_designs');
    }
};
