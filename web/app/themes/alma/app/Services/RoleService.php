<?php

namespace App\Services;

class RoleService
{
    /**
     * Create a new role.
     *
     * @param string $role
     * @param string $displayName
     * @param array $capabilities
     * @return void
     */
    public function createRole(string $role, string $displayName, array $capabilities = []): void
    {
        add_role($role, $displayName, $capabilities);
    }

    /**
     * Assign a role to a user.
     *
     * @param int $userId
     * @param string $role
     * @return void
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
     *
     * @param int|null $userId
     * @param string $role
     * @return bool
     */
    public function hasRole(?int $userId = null, string $role): bool
    {
        $user = $userId ? get_user_by('id', $userId) : wp_get_current_user();

        if (!$user || !$user->exists()) {
            return false;
        }

        return in_array($role, (array) $user->roles);
    }

    /**
     * Check if a user has a specific permission/capability.
     *
     * @param int|null $userId
     * @param string $permission
     * @return bool
     */
    public function hasPermission(?int $userId = null, string $permission): bool
    {
        if ($userId) {
            return user_can($userId, $permission);
        }

        return current_user_can($permission);
    }
}
