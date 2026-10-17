<?php

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Deploy\DeployTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Everything a release needs after its files are in place, in one idempotent command.
 *
 * The deploy script runs this on the server before switching the `current` symlink, so a fatal
 * failure (a migration that does not apply) leaves the previous release serving traffic.
 */
class DeployCommand extends Command
{
    protected $signature = 'alma:deploy
        {--skip=* : Skip a task by its class basename, e.g. --skip=WarmCaches}
        {--lock-timeout=30 : Seconds to wait for another deploy to finish}';

    protected $description = 'Run post-deploy tasks: migrations, module tasks, rewrite flush, cache warm-up.';

    private const LOCK = 'alma_deploy';

    public function handle(): int
    {
        if (! $this->acquireLock((int) $this->option('lock-timeout'))) {
            $this->error('Another deploy holds the lock. Nothing was run.');

            return self::FAILURE;
        }

        try {
            return $this->runTasks();
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * @return list<DeployTask>
     */
    public function tasks(): array
    {
        $skip = (array) $this->option('skip');

        return collect($this->laravel->tagged(DeployTask::TAG))
            ->reject(fn (DeployTask $task) => in_array(class_basename($task), $skip, true))
            ->sortBy(fn (DeployTask $task) => $task->priority())
            ->values()
            ->all();
    }

    private function runTasks(): int
    {
        $failed = 0;

        foreach ($this->tasks() as $task) {
            try {
                $task->run();
                $this->components->twoColumnDetail($task->label(), '<fg=green>done</>');
            } catch (Throwable $e) {
                $this->components->twoColumnDetail($task->label(), $task->fatal() ? '<fg=red>FAILED</>' : '<fg=yellow>failed</>');
                $this->line('  '.$e->getMessage());

                if ($task->fatal()) {
                    $this->error('A fatal task failed. The release must not be activated.');

                    return self::FAILURE;
                }

                $failed++;
            }
        }

        if ($failed > 0) {
            $this->warn("{$failed} non-fatal task(s) failed. The release is usable; check the output above.");
        }

        return self::SUCCESS;
    }

    /**
     * A MySQL named lock, so two deploys (or a deploy and a manual run) never migrate at once.
     * Other drivers (SQLite in tests) have no named locks and run unlocked.
     */
    private function acquireLock(int $timeout): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return true;
        }

        return (int) DB::selectOne('SELECT GET_LOCK(?, ?) AS acquired', [self::LOCK, $timeout])->acquired === 1;
    }

    private function releaseLock(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::selectOne('SELECT RELEASE_LOCK(?)', [self::LOCK]);
        }
    }
}
