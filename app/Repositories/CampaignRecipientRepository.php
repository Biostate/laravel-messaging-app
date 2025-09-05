<?php

namespace App\Repositories;

use App\Enums\CampaignRecipientStatus;
use App\Models\CampaignRecipient;
use App\Repositories\Contracts\CampaignRecipientRepositoryInterface;
use App\Repositories\Traits\HasBaseRepository;
use Illuminate\Database\Eloquent\Collection;

class CampaignRecipientRepository implements CampaignRecipientRepositoryInterface
{
    use HasBaseRepository;

    public function __construct()
    {
        $this->model = CampaignRecipient::class;
    }

    public function getByCampaignId(int $campaignId): Collection
    {
        return $this->newModel()->query()->where('campaign_id', $campaignId)->get();
    }

    public function getByRecipientId(int $recipientId): Collection
    {
        return $this->newModel()->query()->where('recipient_id', $recipientId)->get();
    }

    public function getByStatus(CampaignRecipientStatus $status): Collection
    {
        return $this->newModel()->query()->where('status', $status)->get();
    }

    public function getByStatusWithLimit(CampaignRecipientStatus $status, int $limit): Collection
    {
        return $this->newModel()->query()->where('status', $status)->limit($limit)->get();
    }

    public function getByCampaignAndStatus(int $campaignId, CampaignRecipientStatus $status): Collection
    {
        return $this->newModel()->query()
            ->where('campaign_id', $campaignId)
            ->where('status', $status)
            ->get();
    }
}
