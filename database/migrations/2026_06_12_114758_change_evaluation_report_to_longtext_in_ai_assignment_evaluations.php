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
            $table->longText('evaluation_report')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_assignment_evaluations', function (Blueprint $table) {
            $table->text('evaluation_report')->change();
        });
    }
};
