<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install-application {--seed-dummy-data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will install the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing application...');
        $this->call('migrate:fresh', ['--force' => true]);

        if ($this->option('seed-dummy-data')) {
            $this->call('db:seed', ['--class' => 'CampaignManagementSeeder']);
        }

        $this->info('Application installed successfully!');
    }
}
