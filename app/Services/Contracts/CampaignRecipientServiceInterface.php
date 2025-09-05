<?php

namespace App\Services\Contracts;

use App\Enums\CampaignRecipientStatus;
use Illuminate\Database\Eloquent\Collection;

interface CampaignRecipientServiceInterface extends BaseServiceInterface
{
    public function createCampaignRecipient(int $campaignId, int $recipientId): \Illuminate\Database\Eloquent\Model;

    public function updateStatus(int $id, CampaignRecipientStatus $status): bool;

    public function markAsSent(int $id, string $messageId): bool;

    public function markAsFailed(int $id, string $failureReason): bool;

    public function getByCampaignId(int $campaignId): Collection;

    public function getByRecipientId(int $recipientId): Collection;

    public function getByStatus(CampaignRecipientStatus $status): Collection;

    public function getByStatusWithLimit(CampaignRecipientStatus $status, int $limit): Collection;

    public function getByCampaignAndStatus(int $campaignId, CampaignRecipientStatus $status): Collection;

    public function getPendingByCampaign(int $campaignId): Collection;

    public function getPendingByCampaignWithLimit(int $campaignId, int $limit): Collection;
}
