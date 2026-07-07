<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class GitHubOAuthController extends Controller
{
    /**
     * Redirect to GitHub for OAuth
     */
    public function redirect()
    {
        $state = bin2hex(random_bytes(16));
        session(['github_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => config('services.github.client_id'),
            'redirect_uri' => config('services.github.redirect'),
            'scope' => 'repo,user:email',
            'state' => $state,
            'allow_signup' => 'true',
        ]);

        return redirect('https://github.com/login/oauth/authorize?' . $query);
    }

    /**
     * Handle GitHub OAuth callback
     */
    public function callback(Request $request)
    {
        // Verify state
        if ($request->get('state') !== session('github_oauth_state')) {
            return redirect()->route('login')->withErrors(['github' => 'Invalid OAuth state']);
        }

        // Exchange code for token
        $response = Http::post('https://github.com/login/oauth/access_token', [
            'client_id' => config('services.github.client_id'),
            'client_secret' => config('services.github.client_secret'),
            'code' => $request->get('code'),
            'redirect_uri' => config('services.github.redirect'),
            'state' => $request->get('state'),
        ]);

        $tokenData = [];
        parse_str($response->body(), $tokenData);

        if (!isset($tokenData['access_token'])) {
            return redirect()->route('login')->withErrors(['github' => 'Failed to get access token']);
        }

        $accessToken = $tokenData['access_token'];

        // Get user info from GitHub
        $userResponse = Http::withHeaders([
            'Authorization' => 'token ' . $accessToken,
            'Accept' => 'application/json',
        ])->get('https://api.github.com/user');

        $githubUser = $userResponse->json();

        // Get user email (might be private)
        $emailResponse = Http::withHeaders([
            'Authorization' => 'token ' . $accessToken,
            'Accept' => 'application/json',
        ])->get('https://api.github.com/user/emails');

        $emails = $emailResponse->json();
        $primaryEmail = collect($emails)->firstWhere('primary')['email'] ?? $githubUser['email'] ?? null;

        if (!$primaryEmail) {
            return redirect()->route('login')->withErrors(['github' => 'Could not retrieve email from GitHub']);
        }

        // Find or create user
        $user = User::updateOrCreate(
            ['github_id' => $githubUser['id']],
            [
                'name' => $githubUser['name'] ?? $githubUser['login'],
                'email' => $primaryEmail,
                'github_username' => $githubUser['login'],
                'avatar_url' => $githubUser['avatar_url'],
                'github_token' => $accessToken,
                'password' => bcrypt(bin2hex(random_bytes(32))), // Random password for OAuth users
            ]
        );

        // Update token if user already existed
        if (!$user->wasRecentlyCreated) {
            $user->update([
                'github_token' => $accessToken,
                'avatar_url' => $githubUser['avatar_url'],
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }
}
