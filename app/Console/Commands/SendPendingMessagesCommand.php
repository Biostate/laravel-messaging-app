<?php

namespace App\Console\Commands;

use App\Enums\CampaignRecipientStatus;
use App\Jobs\SendMessageJob;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use Illuminate\Console\Command;

class SendPendingMessagesCommand extends Command
{
    protected $signature = 'messages:send-pending {--limit=10 : Maximum number of messages to process}';

    protected $description = 'Fetch pending messages and dispatch them to the queue';

    public function handle(CampaignRecipientServiceInterface $campaignRecipientService): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Fetching pending messages (limit: {$limit})...");

        $pendingMessages = $campaignRecipientService->getByStatusWithLimit(
            CampaignRecipientStatus::Pending,
            $limit
        );

        if ($pendingMessages->isEmpty()) {
            $this->info('No pending messages found.');

            return self::SUCCESS;
        }

        $this->info("Found {$pendingMessages->count()} pending messages.");

        $dispatchedCount = 0;

        foreach ($pendingMessages as $campaignRecipient) {
            SendMessageJob::dispatch($campaignRecipient);
            $dispatchedCount++;

            $this->line("Dispatched job for campaign recipient ID: {$campaignRecipient->id}");
        }

        $this->info("Successfully dispatched {$dispatchedCount} jobs to the queue.");
        $this->info('Rate limiting is handled by job middleware (2 messages every 5 seconds).');
        $this->info("Run 'php artisan queue:work' to process the jobs.");

        return self::SUCCESS;
    }
}
