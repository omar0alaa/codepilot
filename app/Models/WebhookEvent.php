<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'action',
        'repository_id',
        'pull_request_id',
        'payload',
        'status',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'json',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(Repository::class);
    }

    public function pullRequest(): BelongsTo
    {
        return $this->belongsTo(PullRequest::class);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }
}
