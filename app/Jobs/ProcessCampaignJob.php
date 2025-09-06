<?php

namespace App\Jobs;

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use App\Services\Contracts\CampaignServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ProcessCampaignJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function __construct(
        public Campaign $campaign
    ) {}

    public function uniqueId(): string
    {
        return $this->campaign->id;
    }

    public function handle(): void
    {
        Log::channel('send_message_job')->info("Processing campaign: {$this->campaign->id} - {$this->campaign->name}");

        $campaignService = app(CampaignServiceInterface::class);
        $campaignRecipientService = app(CampaignRecipientServiceInterface::class);

        $campaignService->updateStatus($this->campaign->id, CampaignStatus::Sending);
        Log::channel('send_message_job')->info("Updated campaign {$this->campaign->id} status to sending");

        // TODO: improve this to use chunks in the future
        $pendingRecipients = $campaignRecipientService->getByCampaignAndStatus(
            $this->campaign->id,
            CampaignRecipientStatus::Pending
        );

        if ($pendingRecipients->isEmpty()) {
            Log::channel('send_message_job')->info("No pending recipients found for campaign: {$this->campaign->id}");
            $campaignService->updateStatus($this->campaign->id, CampaignStatus::Completed);

            return;
        }

        Log::channel('send_message_job')->info("Found {$pendingRecipients->count()} pending recipients for campaign: {$this->campaign->id}");
        $jobs = [];
        foreach ($pendingRecipients as $campaignRecipient) {
            $jobs[] = new SendMessageJob($campaignRecipient);
        }

        Bus::batch($jobs)
            ->name('campaign-'.$this->campaign->id)
            ->allowFailures()
            ->then(function (\Illuminate\Bus\Batch $batch) {
                $campaignId = str($batch->name)->after('campaign-')->toString();
                Log::channel('send_message_job')->info("Batch completed: {$campaignId}");
                $campaignService = app(CampaignServiceInterface::class);
                $campaignService->updateStatus($campaignId, CampaignStatus::Completed);
            })
            ->dispatch();

        Log::channel('send_message_job')->info("Dispatch batch with {$pendingRecipients->count()} SendMessageJob instances for campaign: {$this->campaign->id}");
    }
}
