<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $reviewData
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('CodePilot AI: Review Failed')
            ->error()
            ->line('A review has failed for PR #' . ($this->reviewData['pr_number'] ?? ''))
            ->line('Repository: ' . ($this->reviewData['repository'] ?? 'Unknown'))
            ->line('Error: ' . ($this->reviewData['error'] ?? 'Unknown error'))
            ->action('View Details', $this->reviewData['url'] ?? url('/'));
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'review_failed',
            'title' => 'Review failed for PR #' . ($this->reviewData['pr_number'] ?? ''),
            'body' => $this->reviewData['error'] ?? 'Unknown error',
            'url' => $this->reviewData['url'] ?? null,
        ];
    }
}
