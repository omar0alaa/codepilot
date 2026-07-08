<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GitHub\RepositorySyncService;
use App\Services\GitHub\GitHubTokenService;
use App\Services\GitHub\GitHubAppService;
use App\Models\Repository;

class GitHubAppController extends Controller
{
    public function __construct(
        private RepositorySyncService $syncService,
        private GitHubTokenService $tokenService,
        private GitHubAppService $appService
    ) {}

    /**
     * Redirect user to GitHub App installation
     */
    public function install(Request $request)
    {
        $appId = config('services.github.app_id');
        $state = bin2hex(random_bytes(16));
        session(['github_install_state' => $state]);

        $url = "https://github.com/apps/codepilot-ai/installations/new?state={$state}";
        
        return redirect($url);
    }

    /**
     * Handle GitHub App installation callback
     */
    public function callback(Request $request)
    {
        $installationId = $request->get('installation_id');
        $state = $request->get('state');
        
        // Verify state
        if ($state !== session('github_install_state')) {
            return redirect()->route('repositories.index')
                ->withErrors(['installation' => 'Invalid state parameter']);
        }

        if (!$installationId) {
            return redirect()->route('repositories.index')
                ->withErrors(['installation' => 'No installation ID returned']);
        }

        // Store installation ID and sync repositories
        $user = $request->user();
        $user->update(['github_installation_id' => $installationId]);

        // Get installation token and sync repos
        $installationToken = $this->appService->getInstallationToken($installationId);
        $this->tokenService->storeToken($user, $installationToken);

        $result = $this->syncService->syncUserRepositories($user);

        return redirect()->route('repositories.index')
            ->with('success', "Connected {$result['synced']} repositories!");
    }

    /**
     * Sync repositories manually
     */
    public function sync(Request $request)
    {
        $result = $this->syncService->syncUserRepositories($request->user());
        
        return redirect()->route('repositories.index')
            ->with('success', "Synced {$result['synced']} repositories");
    }

    /**
     * Enable review for a repository
     */
    public function enable(Request $request, Repository $repository)
    {
        $this->authorize('update', $repository);
        $this->syncService->enableReview($repository);
        
        return redirect()->back()->with('success', 'Reviews enabled for this repository');
    }

    /**
     * Disable review for a repository
     */
    public function disable(Request $request, Repository $repository)
    {
        $this->authorize('update', $repository);
        $this->syncService->disableReview($repository);
        
        return redirect()->back()->with('success', 'Reviews disabled for this repository');
    }
}
