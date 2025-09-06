<?php

use App\Enums\CampaignRecipientStatus;
use App\Jobs\SendMessageJob;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();

    config([
        'services.webhook-site.auth_key' => 'test-auth-key',
        'services.webhook-site.base_url' => 'https://webhook.site',
        'services.webhook-site.unique_id' => 'test-unique-id',
    ]);
});

describe('SendMessageJob', function () {
    it('has correct job configuration', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $job = new SendMessageJob($campaignRecipient);

        expect($job->timeout)->toBe(60);
        expect($job->tries)->toBe(25);
    });

    it('uses RateLimited middleware', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $job = new SendMessageJob($campaignRecipient);
        $middleware = $job->middleware();

        expect($middleware)->toHaveCount(1);
        expect($middleware[0])->toBeInstanceOf(\Illuminate\Queue\Middleware\RateLimited::class);
    });

    it('dispatches job successfully', function () {
        $campaign = Campaign::factory()->create();
        $recipient = Recipient::factory()->create();
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        SendMessageJob::dispatch($campaignRecipient);

        Queue::assertPushed(SendMessageJob::class, function ($job) use ($campaignRecipient) {
            return $job->campaignRecipient->id === $campaignRecipient->id;
        });
    });

    it('handles successful message sending', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => 'test-message-id-123',
            ], 200),
        ]);

        $job = new SendMessageJob($campaignRecipient);
        $job->handle(new \App\Services\WebhookSiteService);

        $campaignRecipient->refresh();
        expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Sent);
        expect($campaignRecipient->message_id)->toBe('test-message-id-123');
        expect($campaignRecipient->sent_at)->not->toBeNull();

        $cachedData = Cache::get("message_id_{$campaignRecipient->id}");
        expect($cachedData)->not->toBeNull();
        expect($cachedData['messageId'])->toBe('test-message-id-123');
    });

    it('handles failed message sending', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Http::fake([
            '*' => Http::response('Server Error', 500),
        ]);

        $job = new SendMessageJob($campaignRecipient);

        expect(fn () => $job->handle(new \App\Services\WebhookSiteService))->toThrow(\Exception::class);

        $campaignRecipient->refresh();
        expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Failed);
        expect($campaignRecipient->failure_reason)->toContain('Failed to send message via webhook');
    });

    it('handles invalid webhook response', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Http::fake([
            '*' => Http::response(['message' => 'Accepted'], 200),
        ]);

        $job = new SendMessageJob($campaignRecipient);

        expect(fn () => $job->handle(new \App\Services\WebhookSiteService))->toThrow(\Exception::class);

        $campaignRecipient->refresh();
        expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Failed);
        expect($campaignRecipient->failure_reason)->toContain('Invalid response');
    });

    it('handles job failure permanently', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $job = new SendMessageJob($campaignRecipient);
        $exception = new \Exception('Test failure');
        
        $job->failed($exception);

        $campaignRecipient->refresh();
        expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Failed);
        expect($campaignRecipient->failure_reason)->toBe('Test failure');
    });

    it('caches message data in Redis', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => 'test-message-id-456',
            ], 200),
        ]);

        $job = new SendMessageJob($campaignRecipient);
        $job->handle(new \App\Services\WebhookSiteService);

        $cachedData = Cache::get("message_id_{$campaignRecipient->id}");
        expect($cachedData)->not->toBeNull();
        expect($cachedData['messageId'])->toBe('test-message-id-456');
        expect($cachedData['sent_at'])->not->toBeNull();
    });

    it('tracks job timing correctly', function () {
        $campaign = Campaign::factory()->create(['message' => 'Test message']);
        $recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $campaign->id,
            'recipient_id' => $recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        Http::fake([
            '*' => Http::response([
                'message' => 'Accepted',
                'messageId' => 'test-message-id-timing',
            ], 200),
        ]);

        $job = new SendMessageJob($campaignRecipient);
        $job->handle(new \App\Services\WebhookSiteService);

        // Verify the job completed successfully
        $campaignRecipient->refresh();
        expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Sent);
    });
});
