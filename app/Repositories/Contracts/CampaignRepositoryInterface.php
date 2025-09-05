<?php

namespace App\Repositories\Contracts;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Collection;

interface CampaignRepositoryInterface extends BaseRepositoryInterface
{
    public function getByStatus(CampaignStatus $status): Collection;

    public function getByStatusWithLimit(CampaignStatus $status, int $limit): Collection;
}
