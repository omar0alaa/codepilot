<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PullRequest extends Model
{
    protected $fillable = [
        'repository_id',
        'user_id',
        'github_pr_id',
        'number',
        'title',
        'state',
        'head_branch',
        'base_branch',
        'github_url',
        'github_created_at',
        'github_updated_at',
        'github_closed_at',
        'github_merged_at',
    ];

    protected $casts = [
        'github_created_at' => 'datetime',
        'github_updated_at' => 'datetime',
        'github_closed_at' => 'datetime',
        'github_merged_at' => 'datetime',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
