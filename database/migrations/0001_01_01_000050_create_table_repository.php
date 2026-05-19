<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master repository of tables accumulated from approved stories
        Schema::create('table_repository', function (Blueprint $table) {
            $table->id();
            $table->string('table_name')->unique();
            $table->enum('table_type', ['master', 'transactional'])->default('transactional');
            $table->text('description')->nullable();
            $table->foreignId('source_story_id')->nullable()->constrained('stories')->nullOnDelete();
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamps();

            $table->index('table_type');
        });

        Schema::create('column_repository', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_repository_id')->constrained('table_repository')->cascadeOnDelete();
            $table->string('column_name');
            $table->string('data_type')->default('string');
            $table->boolean('is_primary_key')->default(false);
            $table->boolean('is_foreign_key')->default(false);
            $table->string('references_table')->nullable();
            $table->string('references_column')->nullable();
            $table->boolean('is_nullable')->default(true);
            $table->string('default_value')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('source_story_id')->nullable()->constrained('stories')->nullOnDelete();
            $table->timestamps();

            $table->unique(['table_repository_id', 'column_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('column_repository');
        Schema::dropIfExists('table_repository');
    }
};
