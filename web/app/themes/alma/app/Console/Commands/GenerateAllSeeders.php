<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateAllSeeders extends Command
{
    protected $signature = 'seed:generate-all {--prefix=wp_} {--database= : Database connection to use}';
    protected $description = 'Generate seeders for all tables with the specified prefix in the database';

    public function handle()
    {
        $prefix = $this->option('prefix');
        $database = $this->option('database') ?? config('database.default');

        // Set the database connection
        config(['database.default' => $database]);

        // Fetch table names based on database driver
        $driver = config("database.connections.{$database}.driver");
        $databaseName = config("database.connections.{$database}.database");

        try {
            if ($driver === 'mysql') {
                $tables = DB::connection($database)->select('SHOW TABLES');
                $tableNames = array_map('current', $tables);
            } elseif ($driver === 'sqlite') {
                $tables = DB::connection($database)->select("SELECT name FROM sqlite_master WHERE type='table'");
                $tableNames = array_map('current', $tables);
            } else {
                $this->error("Unsupported database driver: {$driver}");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Failed to fetch tables: " . $e->getMessage());
            return 1;
        }

        // Filter tables by prefix
        $tableNames = array_filter($tableNames, fn($table) => Str::startsWith($table, $prefix));

        if (empty($tableNames)) {
            $this->warn("No tables found with prefix: {$prefix} in database: {$databaseName}");
            return 1;
        }

        $this->info('Found tables: ' . implode(', ', $tableNames));

        // Generate seeders using Iseed
        foreach ($tableNames as $table) {
            $this->info("Generating seeder for table: {$table}");
            try {
                $this->call('iseed', [
                    'tables' => $table,
                    '--force' => true, // Overwrite existing seeders
                    '--database' => $database, // Specify connection
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to generate seeder for {$table}: " . $e->getMessage());
            }
        }

        $this->info('All seeders generated successfully.');
        return 0;
    }
}
