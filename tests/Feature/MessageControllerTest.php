<?php

use App\Enums\CampaignRecipientStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('MessageController', function () {
    beforeEach(function () {
        $this->campaign = Campaign::factory()->create(['message' => 'Test campaign message']);
        $this->recipient = Recipient::factory()->create(['phone_number' => '+905551111111']);
        $this->campaignRecipient = CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'status' => CampaignRecipientStatus::Sent,
            'message_id' => 'test-message-id-123',
            'sent_at' => now(),
        ]);
    });

    it('returns list of sent messages', function () {
        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'campaign_id',
                        'recipient_id',
                        'phone_number',
                        'message_content',
                        'status',
                        'sent_at',
                        'message_id',
                    ],
                ],
                'meta',
            ])
            ->assertJson([
                'success' => true,
            ]);
    });

    it('filters messages by status', function () {
        CampaignRecipient::factory()->create([
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        $response = $this->getJson('/api/messages?status=pending');

        $response->assertStatus(200);
        $data = $response->json('data');
        expect($data)->toHaveCount(1);
        expect($data[0]['status'])->toBe('pending');
    });

    it('respects limit parameter', function () {
        CampaignRecipient::factory()->count(5)->create([
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'status' => CampaignRecipientStatus::Sent,
        ]);

        $response = $this->getJson('/api/messages?limit=3');

        $response->assertStatus(200);
        $data = $response->json('data');
        expect($data)->toHaveCount(3);
    });

    it('returns specific message by id', function () {
        $response = $this->getJson("/api/messages/{$this->campaignRecipient->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'campaign_id',
                    'recipient_id',
                    'phone_number',
                    'message_content',
                    'status',
                    'sent_at',
                    'message_id',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->campaignRecipient->id,
                    'phone_number' => '+905551111111',
                    'message_content' => 'Test campaign message',
                    'status' => 'sent',
                ],
            ]);
    });

    it('returns 404 for non-existent message', function () {
        $response = $this->getJson('/api/messages/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Message not found',
            ]);
    });

    it('includes cached message data when available', function () {
        Cache::put("message_id_{$this->campaignRecipient->id}", [
            'messageId' => 'cached-message-id-456',
            'sent_at' => now()->toISOString(),
        ], 3600);

        $response = $this->getJson("/api/messages/{$this->campaignRecipient->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        expect($data['cached_message_id'])->toBe('cached-message-id-456');
        expect($data['cached_sent_at'])->not->toBeNull();
    });

    it('returns message statistics', function () {
        CampaignRecipient::factory()->count(2)->create([
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'status' => CampaignRecipientStatus::Pending,
        ]);

        CampaignRecipient::factory()->count(1)->create([
            'campaign_id' => $this->campaign->id,
            'recipient_id' => $this->recipient->id,
            'status' => CampaignRecipientStatus::Failed,
        ]);

        $response = $this->getJson('/api/messages/stats/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_messages',
                    'sent_messages',
                    'pending_messages',
                    'failed_messages',
                    'success_rate',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_messages' => 4,
                    'sent_messages' => 1,
                    'pending_messages' => 2,
                    'failed_messages' => 1,
                ],
            ]);
    });

    it('handles empty message list', function () {
        CampaignRecipient::query()->delete();

        $response = $this->getJson('/api/messages');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total' => 0,
                ],
            ]);
    });
});
