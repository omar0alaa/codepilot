<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Repository;

class RepositoryPolicy
{
    public function view(User $user, Repository $repository): bool
    {
        return $user->id === $repository->user_id;
    }

    public function update(User $user, Repository $repository): bool
    {
        return $user->id === $repository->user_id;
    }

    public function delete(User $user, Repository $repository): bool
    {
        return $user->id === $repository->user_id;
    }
}
