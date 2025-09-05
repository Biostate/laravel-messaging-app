<?php

namespace App\Repositories;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use App\Repositories\Traits\HasBaseRepository;
use Illuminate\Database\Eloquent\Collection;

class CampaignRepository implements CampaignRepositoryInterface
{
    use HasBaseRepository;

    public function __construct()
    {
        $this->model = Campaign::class;
    }

    public function getByStatus(CampaignStatus $status): Collection
    {
        return $this->newModel()->query()->where('status', $status)->get();
    }

    public function getByStatusWithLimit(CampaignStatus $status, int $limit): Collection
    {
        return $this->newModel()->query()->where('status', $status)->limit($limit)->get();
    }
}
