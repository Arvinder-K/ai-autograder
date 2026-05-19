<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'creation_mode',
        'process_type',
        'status',
        'version',
        'selected_domains',
        'selected_business_units',
        'stakeholders',
        'ai_features',
        'integrations',
        'reporting_needs',
        'architecture',
        'generated_story',
        'ai_analysis',
        'uploaded_document_path',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'selected_domains' => 'array',
            'selected_business_units' => 'array',
            'stakeholders' => 'array',
            'ai_features' => 'array',
            'integrations' => 'array',
            'reporting_needs' => 'array',
            'architecture' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(StoryVersion::class)->orderByDesc('version_number');
    }

    public function quizResponses(): HasMany
    {
        return $this->hasMany(QuizResponse::class);
    }

    public function featureLists(): HasMany
    {
        return $this->hasMany(FeatureList::class);
    }

    public function technicalDesigns(): HasMany
    {
        return $this->hasMany(TechnicalDesign::class);
    }

    public function codingPrompts(): HasMany
    {
        return $this->hasMany(CodingPrompt::class);
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function canBeDeleted(): bool
    {
        return $this->status !== 'approved';
    }

    public function canBeEdited(User $user): bool
    {
        return $this->isOwner($user) && $this->status !== 'approved';
    }
}
