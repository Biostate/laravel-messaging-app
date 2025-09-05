<?php

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Services\CampaignService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mockRepository = \Mockery::mock(CampaignRepositoryInterface::class);
    $this->service = new CampaignService($this->mockRepository);
});

describe('CampaignService', function () {

    describe('getAll() method', function () {
        it('returns all campaigns from repository', function () {
            $campaigns = Campaign::factory()->count(3)->create();
            $this->mockRepository->shouldReceive('all')->once()->andReturn($campaigns);

            $result = $this->service->getAll();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });

        it('returns empty collection when no campaigns exist', function () {
            $this->mockRepository->shouldReceive('all')->once()->andReturn(new Collection);

            $result = $this->service->getAll();

            expect($result)->toBeEmpty();
        });
    });

    describe('getById() method', function () {
        it('returns campaign when found', function () {
            $campaign = Campaign::factory()->create();
            $this->mockRepository->shouldReceive('find')->with($campaign->id)->once()->andReturn($campaign);

            $result = $this->service->getById($campaign->id);

            expect($result)->toBeInstanceOf(Campaign::class);
            expect($result->id)->toBe($campaign->id);
        });

        it('returns null when campaign not found', function () {
            $this->mockRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

            $result = $this->service->getById(999);

            expect($result)->toBeNull();
        });
    });

    describe('create() method', function () {
        it('creates campaign with provided data', function () {
            $data = [
                'name' => 'Test Campaign',
                'message' => 'Test message',
                'status' => CampaignStatus::Draft,
            ];

            $createdCampaign = Campaign::factory()->make($data);
            $this->mockRepository->shouldReceive('create')->with($data)->once()->andReturn($createdCampaign);

            $result = $this->service->create($data);

            expect($result)->toBeInstanceOf(Campaign::class);
            expect($result->name)->toBe('Test Campaign');
        });
    });

    describe('update() method', function () {
        it('updates campaign with provided data', function () {
            $campaign = Campaign::factory()->create();
            $updateData = ['name' => 'Updated Campaign'];

            $this->mockRepository->shouldReceive('update')->with($campaign->id, $updateData)->once()->andReturn(true);

            $result = $this->service->update($campaign->id, $updateData);

            expect($result)->toBeTrue();
        });

        it('returns false when update fails', function () {
            $this->mockRepository->shouldReceive('update')->with(999, [])->once()->andReturn(false);

            $result = $this->service->update(999, []);

            expect($result)->toBeFalse();
        });
    });

    describe('delete() method', function () {
        it('deletes campaign successfully', function () {
            $campaign = Campaign::factory()->create();
            $this->mockRepository->shouldReceive('delete')->with($campaign->id)->once()->andReturn(true);

            $result = $this->service->delete($campaign->id);

            expect($result)->toBeTrue();
        });

        it('returns false when deletion fails', function () {
            $this->mockRepository->shouldReceive('delete')->with(999)->once()->andReturn(false);

            $result = $this->service->delete(999);

            expect($result)->toBeFalse();
        });
    });

    describe('createCampaign() method', function () {
        it('creates campaign with message and optional name', function () {
            $message = 'Test campaign message';
            $name = 'Test Campaign';

            $expectedData = [
                'name' => $name,
                'message' => $message,
                'status' => CampaignStatus::Draft,
            ];

            $createdCampaign = Campaign::factory()->make($expectedData);
            $this->mockRepository->shouldReceive('create')->with($expectedData)->once()->andReturn($createdCampaign);

            $result = $this->service->createCampaign($message, $name);

            expect($result)->toBeInstanceOf(Campaign::class);
            expect($result->message)->toBe($message);
            expect($result->name)->toBe($name);
        });

        it('creates campaign with message only', function () {
            $message = 'Test campaign message';

            $expectedData = [
                'name' => null,
                'message' => $message,
                'status' => CampaignStatus::Draft,
            ];

            $createdCampaign = Campaign::factory()->make($expectedData);
            $this->mockRepository->shouldReceive('create')->with($expectedData)->once()->andReturn($createdCampaign);

            $result = $this->service->createCampaign($message);

            expect($result)->toBeInstanceOf(Campaign::class);
            expect($result->message)->toBe($message);
            expect($result->name)->toBeNull();
        });
    });

    describe('updateStatus() method', function () {
        it('updates campaign status', function () {
            $campaignId = 1;
            $status = CampaignStatus::Sending;

            $this->mockRepository->shouldReceive('update')->with($campaignId, ['status' => $status])->once()->andReturn(true);

            $result = $this->service->updateStatus($campaignId, $status);

            expect($result)->toBeTrue();
        });
    });

    describe('getByStatus() method', function () {
        it('returns campaigns filtered by status', function () {
            $status = CampaignStatus::Draft;
            $campaigns = Campaign::factory()->count(2)->create(['status' => $status]);
            $this->mockRepository->shouldReceive('getByStatus')->with($status)->once()->andReturn($campaigns);

            $result = $this->service->getByStatus($status);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });

        it('returns empty collection when no campaigns with status exist', function () {
            $status = CampaignStatus::Completed;
            $this->mockRepository->shouldReceive('getByStatus')->with($status)->once()->andReturn(new Collection);

            $result = $this->service->getByStatus($status);

            expect($result)->toBeEmpty();
        });
    });

    describe('getByStatusWithLimit() method', function () {
        it('returns campaigns filtered by status with limit', function () {
            $status = CampaignStatus::Draft;
            $limit = 3;
            $campaigns = Campaign::factory()->count(5)->create(['status' => $status]);
            $this->mockRepository->shouldReceive('getByStatusWithLimit')->with($status, $limit)->once()->andReturn($campaigns->take($limit));

            $result = $this->service->getByStatusWithLimit($status, $limit);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });
    });

    describe('status-specific methods', function () {
        it('getDraftCampaigns returns draft campaigns', function () {
            $campaigns = Campaign::factory()->count(2)->create(['status' => CampaignStatus::Draft]);
            $this->mockRepository->shouldReceive('getByStatus')->with(CampaignStatus::Draft)->once()->andReturn($campaigns);

            $result = $this->service->getDraftCampaigns();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(2);
        });

        it('getSendingCampaigns returns sending campaigns', function () {
            $campaigns = Campaign::factory()->count(1)->create(['status' => CampaignStatus::Sending]);
            $this->mockRepository->shouldReceive('getByStatus')->with(CampaignStatus::Sending)->once()->andReturn($campaigns);

            $result = $this->service->getSendingCampaigns();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(1);
        });

        it('getCompletedCampaigns returns completed campaigns', function () {
            $campaigns = Campaign::factory()->count(3)->create(['status' => CampaignStatus::Completed]);
            $this->mockRepository->shouldReceive('getByStatus')->with(CampaignStatus::Completed)->once()->andReturn($campaigns);

            $result = $this->service->getCompletedCampaigns();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });

        it('getFailedCampaigns returns failed campaigns', function () {
            $campaigns = Campaign::factory()->count(1)->create(['status' => CampaignStatus::Failed]);
            $this->mockRepository->shouldReceive('getByStatus')->with(CampaignStatus::Failed)->once()->andReturn($campaigns);

            $result = $this->service->getFailedCampaigns();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(1);
        });
    });

    describe('integration with real repository', function () {
        beforeEach(function () {
            $this->realRepository = new \App\Repositories\CampaignRepository;
            $this->realService = new CampaignService($this->realRepository);
        });

        it('works with real repository for complete flow', function () {
            $campaign = $this->realService->createCampaign('Test message', 'Test Campaign');

            expect($campaign)->toBeInstanceOf(Campaign::class);
            expect($campaign->message)->toBe('Test message');
            expect($campaign->name)->toBe('Test Campaign');
            expect($campaign->status)->toBe(CampaignStatus::Draft);

            $updated = $this->realService->updateStatus($campaign->id, CampaignStatus::Sending);
            expect($updated)->toBeTrue();

            $updatedCampaign = $this->realService->getById($campaign->id);
            expect($updatedCampaign->status)->toBe(CampaignStatus::Sending);
        });

        it('handles status transitions correctly', function () {
            $campaign = $this->realService->createCampaign('Test message');

            $this->realService->updateStatus($campaign->id, CampaignStatus::Sending);
            $sendingCampaign = $this->realService->getById($campaign->id);
            expect($sendingCampaign->status)->toBe(CampaignStatus::Sending);

            $this->realService->updateStatus($campaign->id, CampaignStatus::Completed);
            $completedCampaign = $this->realService->getById($campaign->id);
            expect($completedCampaign->status)->toBe(CampaignStatus::Completed);
        });

        it('works with getByStatus', function () {
            Campaign::factory()->count(3)->create(['status' => CampaignStatus::Draft]);
            Campaign::factory()->count(2)->create(['status' => CampaignStatus::Sending]);

            $draftCampaigns = $this->realService->getByStatus(CampaignStatus::Draft);
            $sendingCampaigns = $this->realService->getByStatus(CampaignStatus::Sending);

            expect($draftCampaigns)->toHaveCount(3);
            expect($sendingCampaigns)->toHaveCount(2);
            expect($draftCampaigns->every(fn($campaign) => $campaign->status === CampaignStatus::Draft))->toBeTrue();
            expect($sendingCampaigns->every(fn($campaign) => $campaign->status === CampaignStatus::Sending))->toBeTrue();
        });

        it('works with getByStatusWithLimit', function () {
            Campaign::factory()->count(5)->create(['status' => CampaignStatus::Draft]);

            $result = $this->realService->getByStatusWithLimit(CampaignStatus::Draft, 3);

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
            expect($result->every(fn($campaign) => $campaign->status === CampaignStatus::Draft))->toBeTrue();
        });
    });
});
