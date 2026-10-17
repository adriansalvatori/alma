<?php

namespace App\Infrastructure\Deploy\Tasks;

use App\Infrastructure\Deploy\DeployTask;

/**
 * Rebuild WordPress' rewrite rules, so a new post type or route works without visiting
 * Settings → Permalinks. A soft flush: on Apache, .htaccess is left alone (the repo ships it).
 */
class FlushRewriteRules implements DeployTask
{
    public function label(): string
    {
        return 'Flush rewrite rules';
    }

    public function priority(): int
    {
        return 90;
    }

    public function fatal(): bool
    {
        return false;
    }

    public function run(): void
    {
        flush_rewrite_rules(false);
    }
}
