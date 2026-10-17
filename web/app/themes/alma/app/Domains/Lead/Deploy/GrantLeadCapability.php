<?php

namespace App\Domains\Lead\Deploy;

use App\Infrastructure\Deploy\DeployTask;
use RuntimeException;

/**
 * Gives administrators and editors the capability every Leads screen checks. Re-applied on each
 * deploy, so a role reset or a fresh database never leaves the team locked out of their leads.
 */
class GrantLeadCapability implements DeployTask
{
    public const CAPABILITY = 'alma_manage_leads';

    public const ROLES = ['administrator', 'editor'];

    public function label(): string
    {
        return 'Grant the Leads capability';
    }

    public function priority(): int
    {
        return 50;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        foreach (self::ROLES as $name) {
            $role = get_role($name);

            if ($role === null) {
                throw new RuntimeException("The {$name} role does not exist.");
            }

            if (! $role->has_cap(self::CAPABILITY)) {
                $role->add_cap(self::CAPABILITY);
            }
        }
    }
}
