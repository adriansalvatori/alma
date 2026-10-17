<?php

namespace App\Infrastructure\Deploy;

/**
 * One step of `wp acorn alma:deploy`.
 *
 * Tasks must be idempotent: the command runs on every release, and running it twice in a row has
 * to leave the site exactly as running it once did. Modules contribute tasks through their
 * provider's $deployTasks.
 */
interface DeployTask
{
    public const TAG = 'alma.deploy-tasks';

    /**
     * Shown in the deploy output.
     */
    public function label(): string;

    /**
     * Lower runs first. Migrations are 0, rewrite flushing 90, cache warming 100.
     */
    public function priority(): int;

    /**
     * A fatal task that fails stops the deploy with a non-zero exit, so the release is never
     * activated. A non-fatal task that fails is reported and the deploy carries on.
     */
    public function fatal(): bool;

    /**
     * Do the work. Throw to report failure.
     */
    public function run(): void;
}
