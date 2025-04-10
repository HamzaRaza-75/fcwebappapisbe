<?php

namespace App\Policies;

use App\Models\Shedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shedule $shedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shedule $shedule): bool
    {
        return $user->id == $shedule->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shedule $shedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shedule $shedule): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shedule $shedule): bool
    {
        return false;
    }
}
