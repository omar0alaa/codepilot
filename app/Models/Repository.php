<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Repository extends Model
{
    protected $fillable = [
        'user_id',
        'github_id',
        'name',
        'full_name',
        'description',
        'is_private',
        'default_branch',
        'is_enabled',
        'settings',
        'github_created_at',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_enabled' => 'boolean',
        'settings' => 'json',
        'github_created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pullRequests(): HasMany
    {
        return $this->hasMany(PullRequest::class);
    }

    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }
}
