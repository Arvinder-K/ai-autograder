<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPrompt extends Model
{
    protected $fillable = [
        'title',
        'module_name',
        'content',
        'original_filename',
    ];

    public function evaluations()
    {
        return $this->hasMany(AIAssignmentEvaluation::class, 'saved_prompt_id');
    }
}
