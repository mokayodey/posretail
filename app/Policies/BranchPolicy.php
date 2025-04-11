<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, Branch $branch)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && $user->branches->contains($branch));
    }

    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Branch $branch)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && $user->branches->contains($branch));
    }

    public function delete(User $user, Branch $branch)
    {
        return $user->hasRole('admin');
    }
} 