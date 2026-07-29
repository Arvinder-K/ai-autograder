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
            $table->string('learner_email')->nullable()->after('student_name');
            $table->string('module_name')->nullable()->after('assignment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_assignment_evaluations', function (Blueprint $table) {
            $table->dropColumn(['learner_email', 'module_name']);
        });
    }
};
