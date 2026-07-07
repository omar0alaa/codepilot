<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'pull_request_id',
        'user_id',
        'status',
        'overall_score',
        'category_scores',
        'issues',
        'suggestions',
        'summary',
        'ai_provider',
        'ai_model',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'overall_score' => 'integer',
        'category_scores' => 'json',
        'issues' => 'json',
        'suggestions' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function pullRequest(): BelongsTo
    {
        return $this->belongsTo(PullRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
