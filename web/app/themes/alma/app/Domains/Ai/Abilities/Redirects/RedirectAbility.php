<?php

namespace App\Domains\Ai\Abilities\Redirects;

use App\Domains\Ai\Abilities\Ability;
use App\Domains\Seo\Admin\RedirectsScreen;
use App\Domains\Seo\SeoServiceProvider;
use WP_Error;

/**
 * Redirect abilities go through the SEO module's RedirectManager, so an agent gets exactly the
 * guards a person in Tools → Redirects gets (no loops, no redirecting wp-admin or the homepage,
 * https-only off-site targets).
 *
 * The Redirects screen needs manage_options, which is far more than an agent should hold. These
 * abilities also accept alma_manage_redirects, a capability that does nothing else and that
 * AI_AGENT_CAN_MANAGE_REDIRECTS grants to the agent user alone.
 */
abstract class RedirectAbility extends Ability
{
    public const CAPABILITY = 'alma_manage_redirects';

    public function category(): string
    {
        return 'alma-seo';
    }

    public function available(): bool
    {
        return app()->providerIsLoaded(SeoServiceProvider::class);
    }

    public function permission(array $input): bool|WP_Error
    {
        return current_user_can(RedirectsScreen::CAPABILITY) || current_user_can(self::CAPABILITY);
    }
}
