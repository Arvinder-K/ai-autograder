<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->enum('format_type', ['business', 'procode', 'agentic']); // Format-01, 02, 03
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'in_review', 'approved', 'archived'])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['story_id', 'format_type']);
        });

        Schema::create('feature_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_list_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sr_no');
            $table->string('feature_cluster');
            $table->string('feature');
            $table->text('detailed_workflow');
            $table->text('feature_description');
            $table->string('table_name')->nullable();
            $table->text('table_column_names')->nullable();
            // Format-02 (pro-code) specific
            $table->string('technology_stack')->nullable();
            $table->string('actor_user')->nullable();
            // Format-03 (agentic) specific
            $table->string('agent_type')->nullable();
            $table->unsignedInteger('step_number')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('feature_list_id');
            $table->index('feature_cluster');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_items');
        Schema::dropIfExists('feature_lists');
    }
};
