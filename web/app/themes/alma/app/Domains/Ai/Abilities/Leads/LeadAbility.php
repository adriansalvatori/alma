<?php

namespace App\Domains\Ai\Abilities\Leads;

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Lead\Deploy\GrantLeadCapability;
use App\Domains\Lead\LeadServiceProvider;
use App\Domains\Lead\Models\Lead;
use WP_Error;

/**
 * Lead abilities read personal data, so all of them need the same capability as the Leads
 * screens (alma_manage_leads), and none registers when the Lead module is switched off.
 */
abstract class LeadAbility extends Ability
{
    public function category(): string
    {
        return 'alma-leads';
    }

    public function available(): bool
    {
        return app()->providerIsLoaded(LeadServiceProvider::class);
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can(GrantLeadCapability::CAPABILITY);
    }

    /**
     * Where a lead came from: the first touch's utm_source, a paid click id, the referring host,
     * or "direct".
     */
    protected function source(Lead $lead): string
    {
        $first = (array) (($lead->attribution ?? [])['first'] ?? []);

        return match (true) {
            ! empty($first['source']) => strtolower((string) $first['source']),
            ! empty($first['gclid']) => 'google (paid)',
            ! empty($first['fbclid']) => 'facebook (paid)',
            ! empty($first['msclkid']) => 'microsoft (paid)',
            ! empty($first['referrer']) => strtolower((string) (parse_url((string) $first['referrer'], PHP_URL_HOST) ?: 'referral')),
            default => 'direct',
        };
    }
}
