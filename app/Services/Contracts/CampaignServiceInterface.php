<?php

namespace App\Services\Contracts;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Collection;

interface CampaignServiceInterface extends BaseServiceInterface
{
    public function createCampaign(string $message, ?string $name = null): \Illuminate\Database\Eloquent\Model;

    public function updateStatus(int $id, CampaignStatus $status): bool;

    public function getByIds(array $ids): Collection;

    public function getByStatus(CampaignStatus $status): Collection;

    public function getByStatusWithLimit(CampaignStatus $status, int $limit): Collection;

    public function getDraftCampaigns(): Collection;

    public function getSendingCampaigns(): Collection;

    public function getCompletedCampaigns(): Collection;

    public function getFailedCampaigns(): Collection;
}
