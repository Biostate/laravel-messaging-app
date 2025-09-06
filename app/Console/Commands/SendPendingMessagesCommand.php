<?php

namespace App\Console\Commands;

use App\Enums\CampaignStatus;
use App\Jobs\ProcessCampaignJob;
use App\Services\Contracts\CampaignServiceInterface;
use Illuminate\Console\Command;

class SendPendingMessagesCommand extends Command
{
    protected $signature = 'messages:send-pending {--campaign=* : Specific campaign IDs}';

    protected $description = 'Fetch pending campaigns and dispatch them';

    public function handle(CampaignServiceInterface $campaignService): int
    {
        $campaignIds = $this->option('campaign');

        if (! empty($campaignIds)) {
            $this->info('Processing specific campaigns: '.implode(', ', $campaignIds));
            $campaigns = $campaignService->getByIds($campaignIds);
        } else {
            $this->info('Fetching campaigns with pending messages...');
            $campaigns = $campaignService->getByStatus(CampaignStatus::Sending);
        }

        if ($campaigns->isEmpty()) {
            $this->info('No campaigns found.');

            return self::SUCCESS;
        }

        $this->info("Found {$campaigns->count()} campaigns with pending messages.");

        foreach ($campaigns as $campaign) {
            ProcessCampaignJob::dispatch($campaign);
            $this->line("Dispatched ProcessCampaignJob for campaign: {$campaign->id} - {$campaign->name}");
        }

        $this->info("Successfully dispatched {$campaigns->count()} ProcessCampaignJob instances.");
        $this->info("Ensure 'php artisan queue:work' is running to process the jobs.");

        return self::SUCCESS;
    }
}
