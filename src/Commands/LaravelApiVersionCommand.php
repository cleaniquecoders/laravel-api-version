<?php

namespace CleaniqueCoders\LaravelApiVersion\Commands;

use Illuminate\Console\Command;

class LaravelApiVersionCommand extends Command
{
    public $signature = 'laravel-api-version';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
