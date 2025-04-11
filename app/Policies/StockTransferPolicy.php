<?php

namespace App\Policies;

use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransferPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function view(User $user, StockTransfer $transfer)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && 
                ($user->branches->contains($transfer->sourceBranch) || 
                 $user->branches->contains($transfer->destinationBranch)));
    }

    public function create(User $user)
    {
        return $user->hasRole(['admin', 'manager']);
    }

    public function approve(User $user, StockTransfer $transfer)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && 
                $user->branches->contains($transfer->destinationBranch));
    }

    public function complete(User $user, StockTransfer $transfer)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && 
                $user->branches->contains($transfer->destinationBranch));
    }

    public function cancel(User $user, StockTransfer $transfer)
    {
        return $user->hasRole('admin') || 
               ($user->hasRole('manager') && 
                ($user->branches->contains($transfer->sourceBranch) || 
                 $user->branches->contains($transfer->destinationBranch)));
    }
} 