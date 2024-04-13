<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GalenaController as Galena;

class GalenaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:galena {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hello! I´m Galena. I am here to help!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = $this->option('message');
        if ($message) {
            $this->info(Galena::ask($message));
        } else {
            $this->info('Hello! I´m Galena. I am here to help! To interact with me, please provide a message. For example: wp acorn app:galena --message="Hello, how are you?"');
        }
    }
}

