<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIAssignmentEvaluation extends Model
{
    protected $table = 'ai_assignment_evaluations';

    protected $fillable = [
        'student_name',
        'assignment_name',
        'prompt_file',
        'zip_file',
        'evaluation_report',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
