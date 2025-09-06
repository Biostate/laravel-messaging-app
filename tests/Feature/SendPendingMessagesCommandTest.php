<?php

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Jobs\ProcessCampaignJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use App\Services\Contracts\CampaignServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

describe('SendPendingMessagesCommand', function () {
    it('dispatches ProcessCampaignJob for campaigns with pending messages', function () {
        $campaign = Campaign::factory()->create([
            'status' => CampaignStatus::Sending,
        ]);
        
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(3)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Artisan::call('messages:send-pending');

        Queue::assertPushed(ProcessCampaignJob::class, 1);
    });

    it('dispatches ProcessCampaignJob for specific campaigns', function () {
        $campaign1 = Campaign::factory()->create([
            'status' => CampaignStatus::Sending,
        ]);
        $campaign2 = Campaign::factory()->create([
            'status' => CampaignStatus::Sending,
        ]);
        
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(2)->create([
            'campaign_id' => $campaign1->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        CampaignRecipient::factory()->count(3)->create([
            'campaign_id' => $campaign2->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Artisan::call('messages:send-pending', ['--campaign' => [$campaign1->id]]);

        Queue::assertPushed(ProcessCampaignJob::class, 1);
    });

    it('handles no campaigns gracefully', function () {
        $output = Artisan::call('messages:send-pending');

        expect($output)->toBe(0);
        Queue::assertNothingPushed();
    });

    it('uses correct service method for campaigns', function () {
        $mockService = Mockery::mock(CampaignServiceInterface::class);
        $mockService->shouldReceive('getByStatus')
            ->with(CampaignStatus::Sending)
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection);

        $this->app->instance(CampaignServiceInterface::class, $mockService);

        Artisan::call('messages:send-pending');
    });

    it('uses correct service method for specific campaigns', function () {
        $mockService = Mockery::mock(CampaignServiceInterface::class);
        $mockService->shouldReceive('getByIds')
            ->with([1, 2])
            ->once()
            ->andReturn(new \Illuminate\Database\Eloquent\Collection);

        $this->app->instance(CampaignServiceInterface::class, $mockService);

        Artisan::call('messages:send-pending', ['--campaign' => [1, 2]]);
    });

    it('outputs correct information', function () {
        $campaign = Campaign::factory()->create([
            'status' => CampaignStatus::Sending,
        ]);
        
        $recipient = Recipient::factory()->create();

        CampaignRecipient::factory()->count(2)->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $output = Artisan::call('messages:send-pending');

        expect($output)->toBe(0);
    });
});
