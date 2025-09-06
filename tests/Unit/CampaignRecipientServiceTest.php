<?php

use App\Enums\CampaignRecipientStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use App\Repositories\Contracts\CampaignRecipientRepositoryInterface;
use App\Services\CampaignRecipientService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mockRepository = \Mockery::mock(CampaignRecipientRepositoryInterface::class);
    $this->service = new CampaignRecipientService($this->mockRepository);
});

describe('CampaignRecipientService', function () {

    describe('getAll() method', function () {
        it('returns all campaign recipients from repository', function () {
            $campaignRecipients = CampaignRecipient::factory()->count(3)->create();
            $this->mockRepository->shouldReceive('all')->once()->andReturn($campaignRecipients);

            $result = $this->service->getAll();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });

        it('returns empty collection when no campaign recipients exist', function () {
            $this->mockRepository->shouldReceive('all')->once()->andReturn(new Collection);

            $result = $this->service->getAll();

            expect($result)->toBeEmpty();
        });
    });

    describe('getById() method', function () {
        it('returns campaign recipient when found', function () {
            $campaignRecipient = CampaignRecipient::factory()->create();
            $this->mockRepository->shouldReceive('find')->with($campaignRecipient->id)->once()->andReturn($campaignRecipient);

            $result = $this->service->getById($campaignRecipient->id);

            expect($result)->toBeInstanceOf(CampaignRecipient::class);
            expect($result->id)->toBe($campaignRecipient->id);
        });

        it('returns null when campaign recipient not found', function () {
            $this->mockRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

            $result = $this->service->getById(999);

            expect($result)->toBeNull();
        });
    });

    describe('create() method', function () {
        it('creates campaign recipient with provided data', function () {
            $data = [
                'campaign_id' => 1,
                'recipient_id' => 1,
                'status' => CampaignRecipientStatus::Pending,
            ];

            $createdCampaignRecipient = CampaignRecipient::factory()->make($data);
            $this->mockRepository->shouldReceive('create')->with($data)->once()->andReturn($createdCampaignRecipient);

            $result = $this->service->create($data);

            expect($result)->toBeInstanceOf(CampaignRecipient::class);
            expect($result->campaign_id)->toBe(1);
        });
    });

    describe('update() method', function () {
        it('updates campaign recipient with provided data', function () {
            $campaignRecipient = CampaignRecipient::factory()->create();
            $updateData = ['status' => CampaignRecipientStatus::Sent];

            $this->mockRepository->shouldReceive('update')->with($campaignRecipient->id, $updateData)->once()->andReturn(true);

            $result = $this->service->update($campaignRecipient->id, $updateData);

            expect($result)->toBeTrue();
        });

        it('returns false when update fails', function () {
            $this->mockRepository->shouldReceive('update')->with(999, [])->once()->andReturn(false);

            $result = $this->service->update(999, []);

            expect($result)->toBeFalse();
        });
    });

    describe('delete() method', function () {
        it('deletes campaign recipient successfully', function () {
            $campaignRecipient = CampaignRecipient::factory()->create();
            $this->mockRepository->shouldReceive('delete')->with($campaignRecipient->id)->once()->andReturn(true);

            $result = $this->service->delete($campaignRecipient->id);

            expect($result)->toBeTrue();
        });

        it('returns false when deletion fails', function () {
            $this->mockRepository->shouldReceive('delete')->with(999)->once()->andReturn(false);

            $result = $this->service->delete(999);

            expect($result)->toBeFalse();
        });
    });

    describe('createCampaignRecipient() method', function () {
        it('creates campaign recipient with campaign and recipient IDs', function () {
            $campaignId = 1;
            $recipientId = 2;

            $expectedData = [
                'campaign_id' => $campaignId,
                'recipient_id' => $recipientId,
                'status' => CampaignRecipientStatus::Pending,
            ];

            $createdCampaignRecipient = CampaignRecipient::factory()->make($expectedData);
            $this->mockRepository->shouldReceive('create')->with($expectedData)->once()->andReturn($createdCampaignRecipient);

            $result = $this->service->createCampaignRecipient($campaignId, $recipientId);

            expect($result)->toBeInstanceOf(CampaignRecipient::class);
            expect($result->campaign_id)->toBe($campaignId);
            expect($result->recipient_id)->toBe($recipientId);
            expect($result->status)->toBe(CampaignRecipientStatus::Pending);
        });
    });

    describe('updateStatus() method', function () {
        it('updates campaign recipient status', function () {
            $campaignRecipientId = 1;
            $status = CampaignRecipientStatus::Sent;

            $this->mockRepository->shouldReceive('update')->with($campaignRecipientId, ['status' => $status])->once()->andReturn(true);

            $result = $this->service->updateStatus($campaignRecipientId, $status);

            expect($result)->toBeTrue();
        });
    });

    describe('markAsSent() method', function () {
        it('marks campaign recipient as sent with message ID', function () {
            $campaignRecipientId = 1;
            $messageId = 'msg-123';

            $this->mockRepository->shouldReceive('update')
                ->with($campaignRecipientId, \Mockery::on(function ($data) use ($messageId) {
                    return $data['status'] === CampaignRecipientStatus::Sent &&
                        $data['message_id'] === $messageId &&
                        isset($data['sent_at']);
                }))
                ->once()
                ->andReturn(true);

            $result = $this->service->markAsSent($campaignRecipientId, $messageId);

            expect($result)->toBeTrue();
        });
    });

    describe('markAsFailed() method', function () {
        it('marks campaign recipient as failed with reason', function () {
            $campaignRecipientId = 1;
            $failureReason = 'Invalid phone number';

            $expectedData = [
                'status' => CampaignRecipientStatus::Failed,
                'failure_reason' => $failureReason,
            ];

            $this->mockRepository->shouldReceive('update')->with($campaignRecipientId, $expectedData)->once()->andReturn(true);

            $result = $this->service->markAsFailed($campaignRecipientId, $failureReason);

            expect($result)->toBeTrue();
        });
    });

    describe('getByCampaignId() method', function () {
        it('returns campaign recipients filtered by campaign ID', function () {
            $campaign = Campaign::factory()->create();
            $campaignRecipients = CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id]);
            $this->mockRepository->shouldReceive('getByCampaignId')->with($campaign->id)->once()->andReturn($campaignRecipients);

            $result = $this->service->getByCampaignId($campaign->id);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });
    });

    describe('getByRecipientId() method', function () {
        it('returns campaign recipients filtered by recipient ID', function () {
            $recipient = Recipient::factory()->create();
            $campaignRecipients = CampaignRecipient::factory()->count(3)->create(['recipient_id' => $recipient->id]);
            $this->mockRepository->shouldReceive('getByRecipientId')->with($recipient->id)->once()->andReturn($campaignRecipients);

            $result = $this->service->getByRecipientId($recipient->id);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });
    });

    describe('getByStatus() method', function () {
        it('returns campaign recipients filtered by status', function () {
            $status = CampaignRecipientStatus::Pending;
            $campaignRecipients = CampaignRecipient::factory()->count(4)->create(['status' => $status]);
            $this->mockRepository->shouldReceive('getByStatus')->with($status)->once()->andReturn($campaignRecipients);

            $result = $this->service->getByStatus($status);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(4);
        });
    });

    describe('getByStatusWithLimit() method', function () {
        it('returns campaign recipients filtered by status with limit', function () {
            $status = CampaignRecipientStatus::Pending;
            $limit = 2;
            $campaignRecipients = CampaignRecipient::factory()->count(5)->create(['status' => $status]);
            $this->mockRepository->shouldReceive('getByStatusWithLimit')->with($status, $limit)->once()->andReturn($campaignRecipients->take($limit));

            $result = $this->service->getByStatusWithLimit($status, $limit);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });
    });

    describe('getByCampaignAndStatus() method', function () {
        it('returns campaign recipients filtered by campaign and status', function () {
            $campaign = Campaign::factory()->create();
            $status = CampaignRecipientStatus::Sent;
            $campaignRecipients = CampaignRecipient::factory()->count(3)->create(['campaign_id' => $campaign->id, 'status' => $status]);
            $this->mockRepository->shouldReceive('getByCampaignAndStatus')->with($campaign->id, $status)->once()->andReturn($campaignRecipients);

            $result = $this->service->getByCampaignAndStatus($campaign->id, $status);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });
    });

    describe('getPendingByCampaign() method', function () {
        it('returns pending campaign recipients for campaign', function () {
            $campaign = Campaign::factory()->create();
            $campaignRecipients = CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id, 'status' => CampaignRecipientStatus::Pending]);
            $this->mockRepository->shouldReceive('getByCampaignAndStatus')->with($campaign->id, CampaignRecipientStatus::Pending)->once()->andReturn($campaignRecipients);

            $result = $this->service->getPendingByCampaign($campaign->id);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });
    });

    describe('getPendingByCampaignWithLimit() method', function () {
        it('returns pending campaign recipients for campaign with limit', function () {
            $campaign = Campaign::factory()->create();
            $limit = 2;
            $campaignRecipients = CampaignRecipient::factory()->count(5)->create(['campaign_id' => $campaign->id, 'status' => CampaignRecipientStatus::Pending]);
            $this->mockRepository->shouldReceive('getByCampaignAndStatus')->with($campaign->id, CampaignRecipientStatus::Pending)->once()->andReturn($campaignRecipients);

            $result = $this->service->getPendingByCampaignWithLimit($campaign->id, $limit);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });
    });

    describe('integration with real repository', function () {
        beforeEach(function () {
            $this->realRepository = new \App\Repositories\CampaignRecipientRepository;
            $this->realService = new CampaignRecipientService($this->realRepository);
        });

        it('works with real repository for complete flow', function () {
            $campaign = Campaign::factory()->create();
            $recipient = Recipient::factory()->create();

            $campaignRecipient = $this->realService->createCampaignRecipient($campaign->id, $recipient->id);

            expect($campaignRecipient)->toBeInstanceOf(CampaignRecipient::class);
            expect($campaignRecipient->campaign_id)->toBe($campaign->id);
            expect($campaignRecipient->recipient_id)->toBe($recipient->id);
            expect($campaignRecipient->status)->toBe(CampaignRecipientStatus::Pending);

            $updated = $this->realService->markAsSent($campaignRecipient->id, 'msg-123');
            expect($updated)->toBeTrue();

            $updatedCampaignRecipient = $this->realService->getById($campaignRecipient->id);
            expect($updatedCampaignRecipient->status)->toBe(CampaignRecipientStatus::Sent);
            expect($updatedCampaignRecipient->message_id)->toBe('msg-123');
        });

        it('handles status transitions correctly', function () {
            $campaign = Campaign::factory()->create();
            $recipient = Recipient::factory()->create();
            $campaignRecipient = $this->realService->createCampaignRecipient($campaign->id, $recipient->id);

            $this->realService->markAsSent($campaignRecipient->id, 'msg-123');
            $sentCampaignRecipient = $this->realService->getById($campaignRecipient->id);
            expect($sentCampaignRecipient->status)->toBe(CampaignRecipientStatus::Sent);

            $this->realService->markAsFailed($campaignRecipient->id, 'Test failure');
            $failedCampaignRecipient = $this->realService->getById($campaignRecipient->id);
            expect($failedCampaignRecipient->status)->toBe(CampaignRecipientStatus::Failed);
        });

        it('works with getByCampaignId', function () {
            $campaign = Campaign::factory()->create();
            CampaignRecipient::factory()->count(3)->create(['campaign_id' => $campaign->id]);
            CampaignRecipient::factory()->count(2)->create();

            $result = $this->realService->getByCampaignId($campaign->id);
            expect($result)->toHaveCount(3);
        });

        it('works with getByStatus', function () {
            CampaignRecipient::factory()->count(4)->create(['status' => CampaignRecipientStatus::Pending]);
            CampaignRecipient::factory()->count(2)->create(['status' => CampaignRecipientStatus::Sent]);

            $pendingResult = $this->realService->getByStatus(CampaignRecipientStatus::Pending);
            $sentResult = $this->realService->getByStatus(CampaignRecipientStatus::Sent);

            expect($pendingResult)->toHaveCount(4);
            expect($sentResult)->toHaveCount(2);
            expect($pendingResult->every(fn ($cr) => $cr->status === CampaignRecipientStatus::Pending))->toBeTrue();
            expect($sentResult->every(fn ($cr) => $cr->status === CampaignRecipientStatus::Sent))->toBeTrue();
        });

        it('works with getPendingByCampaign', function () {
            $campaign = Campaign::factory()->create();
            CampaignRecipient::factory()->count(3)->create(['campaign_id' => $campaign->id, 'status' => CampaignRecipientStatus::Pending]);
            CampaignRecipient::factory()->count(2)->create(['campaign_id' => $campaign->id, 'status' => CampaignRecipientStatus::Sent]);

            $result = $this->realService->getPendingByCampaign($campaign->id);
            expect($result)->toHaveCount(3);
            expect($result->every(fn ($cr) => $cr->status === CampaignRecipientStatus::Pending))->toBeTrue();
        });
    });
});
