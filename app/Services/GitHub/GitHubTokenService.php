<?php

namespace App\Services\GitHub;

use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class GitHubTokenService
{
    /**
     * Encrypt and store a GitHub token for a user
     */
    public function storeToken(User $user, string $token, ?string $refreshToken = null): void
    {
        $user->update([
            'github_token' => Crypt::encryptString($token),
            'github_refresh_token' => $refreshToken ? Crypt::encryptString($refreshToken) : null,
        ]);
    }

    /**
     * Retrieve and decrypt a user's GitHub token
     */
    public function getToken(User $user): ?string
    {
        if (!$user->github_token) {
            return null;
        }

        try {
            return Crypt::decryptString($user->github_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve and decrypt a user's GitHub refresh token
     */
    public function getRefreshToken(User $user): ?string
    {
        if (!$user->github_refresh_token) {
            return null;
        }

        try {
            return Crypt::decryptString($user->github_refresh_token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if user has a valid GitHub token
     */
    public function hasToken(User $user): bool
    {
        return !empty($user->github_token);
    }

    /**
     * Clear stored tokens
     */
    public function clearTokens(User $user): void
    {
        $user->update([
            'github_token' => null,
            'github_refresh_token' => null,
        ]);
    }
}
