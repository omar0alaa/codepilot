<?php

namespace App\Services\Analytics;

use App\Models\Review;
use App\Models\PullRequest;
use App\Models\Repository;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get overall quality trend for a user (last 30 days)
     */
    public function getQualityTrend(User $user, int $days = 30): array
    {
        $repoIds = $user->repositories()->pluck('id');
        
        $trend = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subDays($days))
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('AVG(overall_score) as avg_score'),
            DB::raw('COUNT(*) as review_count')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return $trend->map(fn($item) => [
            'date' => $item->date,
            'avg_score' => round($item->avg_score),
            'review_count' => $item->review_count,
        ])->toArray();
    }

    /**
     * Get security trend for a user
     */
    public function getSecurityTrend(User $user, int $days = 30): array
    {
        $repoIds = $user->repositories()->pluck('id');
        
        $reviews = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })
        ->where('status', 'completed')
        ->where('created_at', '>=', now()->subDays($days))
        ->get();

        $securityScores = [];
        foreach ($reviews as $review) {
            $date = $review->created_at->format('Y-m-d');
            $categoryScore = $review->category_scores['security'] ?? null;
            if ($categoryScore !== null) {
                $securityScores[$date][] = $categoryScore;
            }
        }

        $trend = [];
        foreach ($securityScores as $date => $scores) {
            $trend[] = [
                'date' => $date,
                'avg_security_score' => round(array_sum($scores) / count($scores)),
            ];
        }

        return $trend;
    }

    /**
     * Get technical debt metrics
     */
    public function getTechnicalDebt(User $user): array
    {
        $repoIds = $user->repositories()->pluck('id');
        
        $reviews = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->limit(100)
        ->get();

        $totalIssues = 0;
        $criticalIssues = 0;
        $warningIssues = 0;
        $infoIssues = 0;

        foreach ($reviews as $review) {
            $issues = $review->issues ?? [];
            $totalIssues += count($issues);
            
            foreach ($issues as $issue) {
                $severity = $issue['severity'] ?? 'info';
                match($severity) {
                    'critical' => $criticalIssues++,
                    'warning' => $warningIssues++,
                    default => $infoIssues++,
                };
            }
        }

        return [
            'total_issues' => $totalIssues,
            'critical' => $criticalIssues,
            'warning' => $warningIssues,
            'info' => $infoIssues,
            'technical_debt_score' => $criticalIssues * 5 + $warningIssues * 2 + $infoIssues,
        ];
    }

    /**
     * Get common problems across repositories
     */
    public function getCommonProblems(User $user, int $limit = 10): array
    {
        $repoIds = $user->repositories()->pluck('id');
        
        $reviews = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })
        ->where('status', 'completed')
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();

        $problemCounts = [];
        foreach ($reviews as $review) {
            $issues = $review->issues ?? [];
            foreach ($issues as $issue) {
                $title = $issue['title'] ?? 'Unknown';
                $category = $issue['category'] ?? 'general';
                $key = $category . ':' . $title;
                $problemCounts[$key] = ($problemCounts[$key] ?? 0) + 1;
            }
        }

        arsort($problemCounts);

        return array_slice(array_map(function ($count, $key) {
            [$category, $title] = explode(':', $key, 2);
            return ['category' => $category, 'title' => $title, 'count' => $count];
        }, $problemCounts, array_keys($problemCounts)), 0, $limit);
    }

    /**
     * Get repository health score
     */
    public function getRepositoryHealth(Repository $repository): array
    {
        $reviews = $repository->pullRequests()
            ->with('reviews')
            ->get()
            ->flatMap->reviews
            ->where('status', 'completed');

        $reviewCount = $reviews->count();
        $avgScore = $reviews->avg('overall_score') ?? 0;

        $categoryScores = [];
        foreach ($reviews as $review) {
            foreach (($review->category_scores ?? []) as $category => $score) {
                $categoryScores[$category][] = $score;
            }
        }

        $health = [
            'overall' => round($avgScore),
            'review_count' => $reviewCount,
            'categories' => [],
            'grade' => $this->getGrade($avgScore),
        ];

        foreach ($categoryScores as $category => $scores) {
            $avg = round(array_sum($scores) / count($scores));
            $health['categories'][$category] = $avg;
        }

        return $health;
    }

    /**
     * Get developer metrics
     */
    public function getDeveloperMetrics(User $user): array
    {
        $repoIds = $user->repositories()->pluck('id');
        
        $prStats = PullRequest::whereIn('repository_id', $repoIds)
            ->select(
                DB::raw('COUNT(*) as total_prs'),
                DB::raw('SUM(CASE WHEN state = "open" THEN 1 ELSE 0 END) as open_prs'),
                DB::raw('SUM(CASE WHEN state = "closed" THEN 1 ELSE 0 END) as closed_prs')
            )
            ->first();

        $reviewStats = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })
        ->select(
            DB::raw('COUNT(*) as total_reviews'),
            DB::raw('AVG(CASE WHEN status = "completed" THEN overall_score ELSE NULL END) as avg_score'),
            DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_reviews')
        )
        ->first();

        return [
            'total_prs' => $prStats->total_prs ?? 0,
            'open_prs' => $prStats->open_prs ?? 0,
            'closed_prs' => $prStats->closed_prs ?? 0,
            'total_reviews' => $reviewStats->total_reviews ?? 0,
            'avg_score' => round($reviewStats->avg_score ?? 0),
            'failed_reviews' => $reviewStats->failed_reviews ?? 0,
            'review_frequency' => $this->getReviewFrequency($repoIds),
        ];
    }

    /**
     * Get review frequency (reviews per week)
     */
    private function getReviewFrequency($repoIds): float
    {
        $reviewCount = Review::whereIn('pull_request_id', function ($q) use ($repoIds) {
            $q->select('id')->from('pull_requests')
                ->whereIn('repository_id', $repoIds);
        })->count();

        $weeksActive = max(1, $this->getWeeksActive($repoIds));
        
        return round($reviewCount / $weeksActive, 1);
    }

    private function getWeeksActive($repoIds): int
    {
        $oldest = PullRequest::whereIn('repository_id', $repoIds)
            ->orderBy('github_created_at')
            ->first();

        if (!$oldest || !$oldest->github_created_at) {
            return 1;
        }

        return $oldest->github_created_at->diffInWeeks(now());
    }

    /**
     * Get letter grade from score
     */
    private function getGrade(float $score): string
    {
        return match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }
}
