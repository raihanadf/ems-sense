<?php

namespace App\Policies;

use App\Models\User;

class ModelSettingsPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user can view the model settings.
     */
    public function viewRole(User $user): bool
    {
        return $user->is_curator();
    }

    /**
     * Determine if the user can manage the model settings.
     */
    public function manage(User $user): bool
    {
        return $user->is_curator();
    }
}
