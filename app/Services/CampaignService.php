<?php

namespace App\Services;

use App\Enums\CampaignStatus;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Services\Contracts\CampaignServiceInterface;
use App\Services\Traits\HasBaseService;
use Illuminate\Database\Eloquent\Collection;

class CampaignService implements CampaignServiceInterface
{
    use HasBaseService;

    public function __construct(CampaignRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createCampaign(string $message, ?string $name = null): \Illuminate\Database\Eloquent\Model
    {
        return $this->create([
            'name' => $name,
            'message' => $message,
            'status' => CampaignStatus::Draft,
        ]);
    }

    public function updateStatus(int $id, CampaignStatus $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function getByIds(array $ids): Collection
    {
        return $this->repository->getByIds($ids);
    }

    public function getByStatus(CampaignStatus $status): Collection
    {
        return $this->repository->getByStatus($status);
    }

    public function getByStatusWithLimit(CampaignStatus $status, int $limit): Collection
    {
        return $this->repository->getByStatusWithLimit($status, $limit);
    }

    public function getDraftCampaigns(): Collection
    {
        return $this->getByStatus(CampaignStatus::Draft);
    }

    public function getSendingCampaigns(): Collection
    {
        return $this->getByStatus(CampaignStatus::Sending);
    }

    public function getCompletedCampaigns(): Collection
    {
        return $this->getByStatus(CampaignStatus::Completed);
    }

    public function getFailedCampaigns(): Collection
    {
        return $this->getByStatus(CampaignStatus::Failed);
    }
}
