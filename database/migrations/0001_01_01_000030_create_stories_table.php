<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('creation_mode', ['quiz', 'description'])->default('quiz');
            $table->enum('process_type', ['single', 'multi'])->default('single');
            $table->enum('status', ['draft', 'in_review', 'approved', 'archived'])->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->json('selected_domains')->nullable();
            $table->json('selected_business_units')->nullable();
            $table->json('stakeholders')->nullable();
            $table->json('ai_features')->nullable();
            $table->json('integrations')->nullable();
            $table->json('reporting_needs')->nullable();
            $table->json('architecture')->nullable();
            $table->longText('generated_story')->nullable();
            $table->longText('ai_analysis')->nullable();
            $table->text('uploaded_document_path')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
