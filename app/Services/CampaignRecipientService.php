<?php

namespace App\Services;

use App\Enums\CampaignRecipientStatus;
use App\Repositories\Contracts\CampaignRecipientRepositoryInterface;
use App\Services\Contracts\CampaignRecipientServiceInterface;
use App\Services\Traits\HasBaseService;
use Illuminate\Database\Eloquent\Collection;

class CampaignRecipientService implements CampaignRecipientServiceInterface
{
    use HasBaseService;

    public function __construct(CampaignRecipientRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createCampaignRecipient(int $campaignId, int $recipientId): \Illuminate\Database\Eloquent\Model
    {
        return $this->create([
            'campaign_id' => $campaignId,
            'recipient_id' => $recipientId,
            'status' => CampaignRecipientStatus::Pending,
        ]);
    }

    public function updateStatus(int $id, CampaignRecipientStatus $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    public function markAsSent(int $id, string $messageId): bool
    {
        return $this->update($id, [
            'status' => CampaignRecipientStatus::Sent,
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(int $id, string $failureReason): bool
    {
        return $this->update($id, [
            'status' => CampaignRecipientStatus::Failed,
            'failure_reason' => $failureReason,
        ]);
    }

    public function getByCampaignId(int $campaignId): Collection
    {
        return $this->repository->getByCampaignId($campaignId);
    }

    public function getByRecipientId(int $recipientId): Collection
    {
        return $this->repository->getByRecipientId($recipientId);
    }

    public function getByStatus(CampaignRecipientStatus $status): Collection
    {
        return $this->repository->getByStatus($status);
    }

    public function getByStatusWithLimit(CampaignRecipientStatus $status, int $limit): Collection
    {
        return $this->repository->getByStatusWithLimit($status, $limit);
    }

    public function getByCampaignAndStatus(int $campaignId, CampaignRecipientStatus $status): Collection
    {
        return $this->repository->getByCampaignAndStatus($campaignId, $status);
    }

    public function getPendingByCampaign(int $campaignId): Collection
    {
        return $this->getByCampaignAndStatus($campaignId, CampaignRecipientStatus::Pending);
    }

    public function getPendingByCampaignWithLimit(int $campaignId, int $limit): Collection
    {
        $pendingRecipients = $this->getByCampaignAndStatus($campaignId, CampaignRecipientStatus::Pending);

        return $pendingRecipients->take($limit);
    }
}
