<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwaggerGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger API documentation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating Swagger API documentation...');
        $this->call('l5-swagger:generate');

        $docsJsonPath = storage_path('api-docs/api-docs.json');
        $appUrl = config('app.url');

        $this->info('Swagger documentation generated successfully.');
        $this->info('View the documentation at:');
        $this->line('- <fg=cyan>'.$appUrl.'/api/documentation</>');
        $this->info('View Documentation file at:');
        $this->line('- <fg=cyan>'.$docsJsonPath.'</>');

        return self::SUCCESS;
    }
}
