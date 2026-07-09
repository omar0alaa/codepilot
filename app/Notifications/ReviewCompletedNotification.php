<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewCompletedNotification extends Notification implements ShouldQueue
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
        $score = $this->reviewData['overall_score'] ?? 0;
        $repoName = $this->reviewData['repository'] ?? 'Unknown';
        $prNumber = $this->reviewData['pr_number'] ?? '';
        $prTitle = $this->reviewData['pr_title'] ?? '';
        $issueCount = count($this->reviewData['issues'] ?? []);

        $scoreColor = $score >= 80 ? 'green' : ($score >= 50 ? 'orange' : 'red');

        return (new MailMessage)
            ->subject("CodePilot AI: Review for #{$prNumber} {$prTitle}")
            ->greeting("Review Completed for {$repoName}")
            ->line("Pull Request #{$prNumber}: {$prTitle}")
            ->line("Overall Score: {$score}/100")
            ->line("Issues Found: {$issueCount}")
            ->action('View Review', $this->reviewData['url'] ?? url('/'))
            ->line('Thank you for using CodePilot AI!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'review_completed',
            'title' => 'Review completed for PR #' . ($this->reviewData['pr_number'] ?? ''),
            'body' => 'Score: ' . ($this->reviewData['overall_score'] ?? 0) . '/100 — ' . count($this->reviewData['issues'] ?? []) . ' issue(s) found',
            'url' => $this->reviewData['url'] ?? null,
            'data' => $this->reviewData,
        ];
    }
}
