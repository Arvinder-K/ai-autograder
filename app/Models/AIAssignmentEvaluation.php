<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIAssignmentEvaluation extends Model
{
    protected $table = 'ai_assignment_evaluations';

    protected $fillable = [
        'student_name',
        'learner_email',
        'assignment_name',
        'module_name',
        'prompt_file',
        'saved_prompt_id',
        'zip_file',
        'evaluation_report',
        'status',
    ];

    public function savedPrompt()
    {
        return $this->belongsTo(SavedPrompt::class, 'saved_prompt_id');
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
