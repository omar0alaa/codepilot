<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repository;
use App\Services\GitHub\GitHubApiService;

class RepositoryController extends Controller
{
    public function __construct(
        private GitHubApiService $githubApi
    ) {}

    public function index(Request $request)
    {
        $repositories = $request->user()->repositories()->orderBy('updated_at', 'desc')->paginate(20);
        return view('repositories.index', compact('repositories'));
    }

    public function connect(Request $request)
    {
        // Redirect to GitHub App installation
        $appId = config('services.github.app_id');
        $redirectUrl = "https://github.com/apps/codepilot-ai/installations/new";
        return redirect($redirectUrl);
    }

    public function install(Request $request)
    {
        // Handle GitHub App installation callback
        $installationId = $request->get('installation_id');
        
        if (!$installationId) {
            return redirect()->route('repositories.index')->withErrors(['installation' => 'Installation failed']);
        }

        // Fetch installation repos from GitHub API
        $repos = $this->githubApi->getInstallationRepositories($installationId);
        
        foreach ($repos['repositories'] ?? [] as $repoData) {
            Repository::updateOrCreate(
                ['github_id' => $repoData['id']],
                [
                    'user_id' => $request->user()->id,
                    'name' => $repoData['name'],
                    'full_name' => $repoData['full_name'],
                    'description' => $repoData['description'],
                    'is_private' => $repoData['private'],
                    'default_branch' => $repoData['default_branch'],
                    'github_created_at' => $repoData['created_at'],
                ]
            );
        }

        return redirect()->route('repositories.index')->with('success', 'Repositories connected successfully');
    }

    public function show(Repository $repository)
    {
        $this->authorize('view', $repository);
        
        $pullRequests = $repository->pullRequests()->orderBy('github_created_at', 'desc')->paginate(20);
        return view('repositories.show', compact('repository', 'pullRequests'));
    }

    public function settings(Repository $repository)
    {
        $this->authorize('view', $repository);
        return view('repositories.settings', compact('repository'));
    }

    public function updateSettings(Request $request, Repository $repository)
    {
        $this->authorize('update', $repository);
        
        $validated = $request->validate([
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ]);

        $repository->update($validated);
        
        return redirect()->route('repositories.settings', $repository)->with('success', 'Settings updated');
    }

    public function destroy(Repository $repository)
    {
        $this->authorize('delete', $repository);
        $repository->delete();
        
        return redirect()->route('repositories.index')->with('success', 'Repository removed');
    }
}
