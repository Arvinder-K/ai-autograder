<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_assignment_evaluations', function (Blueprint $table) {
            $table->unsignedBigInteger('saved_prompt_id')->nullable()->after('id');
            
            $table->foreign('saved_prompt_id')
                  ->references('id')
                  ->on('saved_prompts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_assignment_evaluations', function (Blueprint $table) {
            $table->dropForeign(['saved_prompt_id']);
            $table->dropColumn('saved_prompt_id');
        });
    }
};
