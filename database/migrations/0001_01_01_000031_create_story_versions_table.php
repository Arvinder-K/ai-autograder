<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->longText('snapshot_data'); // Full JSON snapshot of the story at this version
            $table->string('change_summary')->nullable();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['story_id', 'version_number']);
            $table->index('story_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_versions');
    }
};
