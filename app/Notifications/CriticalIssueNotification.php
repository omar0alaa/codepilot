<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CriticalIssueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $issueData
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🚨 CodePilot AI: Critical Issue Detected')
            ->line('A critical security issue was found in PR #' . ($this->issueData['pr_number'] ?? ''))
            ->line('Repository: ' . ($this->issueData['repository'] ?? 'Unknown'))
            ->line('Issue: ' . ($this->issueData['title'] ?? ''))
            ->line('File: ' . ($this->issueData['file'] ?? 'unknown'))
            ->action('View Review', $this->issueData['url'] ?? url('/'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'critical_issue',
            'title' => '🚨 Critical issue in PR #' . ($this->issueData['pr_number'] ?? ''),
            'body' => ($this->issueData['title'] ?? '') . ' in ' . ($this->issueData['file'] ?? 'unknown'),
            'url' => $this->issueData['url'] ?? null,
        ];
    }
}
