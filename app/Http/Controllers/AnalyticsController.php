<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Analytics\AnalyticsService;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $qualityTrend = $this->analytics->getQualityTrend($user);
        $securityTrend = $this->analytics->getSecurityTrend($user);
        $technicalDebt = $this->analytics->getTechnicalDebt($user);
        $commonProblems = $this->analytics->getCommonProblems($user);
        $developerMetrics = $this->analytics->getDeveloperMetrics($user);

        $repositoryHealth = [];
        foreach ($user->repositories as $repo) {
            $repositoryHealth[] = [
                'name' => $repo->full_name,
                'health' => $this->analytics->getRepositoryHealth($repo),
            ];
        }

        return view('analytics.index', compact(
            'qualityTrend',
            'securityTrend',
            'technicalDebt',
            'commonProblems',
            'developerMetrics',
            'repositoryHealth'
        ));
    }

    public function repositoryHealth(Request $request, $repository)
    {
        $repo = $request->user()->repositories()->findOrFail($repository);
        $health = $this->analytics->getRepositoryHealth($repo);

        return response()->json($health);
    }
}
