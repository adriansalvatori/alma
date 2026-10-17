<?php

namespace App\Domains\Account\Services;

class RoleService
{
    /**
     * Create a new role.
     */
    public function createRole(string $role, string $displayName, array $capabilities = []): void
    {
        add_role($role, $displayName, $capabilities);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(int $userId, string $role): void
    {
        $user = get_user_by('id', $userId);
        if ($user) {
            $user->set_role($role);
        }
    }

    /**
     * Check if a user has a specific role.
     */
    public function hasRole(?int $userId, string $role): bool
    {
        $user = $userId ? get_user_by('id', $userId) : wp_get_current_user();

        if (! $user || ! $user->exists()) {
            return false;
        }

        return in_array($role, (array) $user->roles);
    }

    /**
     * Check if a user has a specific permission/capability.
     */
    public function hasPermission(?int $userId, string $permission): bool
    {
        if ($userId) {
            return user_can($userId, $permission);
        }

        return current_user_can($permission);
    }
}
