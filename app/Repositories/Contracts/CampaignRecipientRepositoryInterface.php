<?php

namespace App\Repositories\Contracts;

use App\Enums\CampaignRecipientStatus;
use Illuminate\Database\Eloquent\Collection;

interface CampaignRecipientRepositoryInterface extends BaseRepositoryInterface
{
    public function getByCampaignId(int $campaignId): Collection;

    public function getByRecipientId(int $recipientId): Collection;

    public function getByStatus(CampaignRecipientStatus $status): Collection;

    public function getByStatusWithLimit(CampaignRecipientStatus $status, int $limit): Collection;

    public function getByCampaignAndStatus(int $campaignId, CampaignRecipientStatus $status): Collection;
}
