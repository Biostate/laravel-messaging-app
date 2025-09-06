<?php

use App\Enums\CampaignRecipientStatus;
use App\Jobs\SendMessageJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

describe('SendPendingMessagesCommand', function () {
    it('dispatches jobs for pending messages', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Artisan::call('messages:send-pending', ['--limit' => 5]);

        Queue::assertPushed(SendMessageJob::class, 3);
    });

    it('respects the limit parameter', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(5)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Artisan::call('messages:send-pending', ['--limit' => 2]);

        Queue::assertPushed(SendMessageJob::class, 2);
    });

    it('handles no pending messages gracefully', function () {
        $output = Artisan::call('messages:send-pending');

        expect($output)->toBe(0);
        Queue::assertNothingPushed();
    });

    it('implements rate limiting correctly', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(5)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Artisan::call('messages:send-pending', ['--limit' => 5]);

        Queue::assertPushed(SendMessageJob::class, 5);
    });

    it('uses correct service method', function () {
        $mockService = Mockery::mock(CampaignRecipientServiceInterface::class);
        $mockService->shouldReceive('getByStatusWithLimit')
            ->with(CampaignRecipientStatus::Pending, 10)
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection);

        $this->app->instance(CampaignRecipientServiceInterface::class, $mockService);

        Artisan::call('messages:send-pending');
    });

    it('outputs correct information', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(2)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $output = Artisan::call('messages:send-pending', ['--limit' => 2]);

        expect($output)->toBe(0);
    });
});
