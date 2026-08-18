<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {--dry : Print config only without sending mail}';

    protected $description = 'Smoke-test mail configuration (placeholder; extend to send test message).';

    public function handle(): int
    {
        if ($this->option('dry')) {
            $this->info('Mail driver: ' . config('mail.default'));

            return self::SUCCESS;
        }

        $this->warn('No test message is configured. Run with --dry to inspect mail.default.');

        return self::SUCCESS;
    }
}
