<?php

namespace App\Services;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\Contracts\MessageServiceInterface;
use App\Services\Traits\HasBaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MessageService implements MessageServiceInterface
{
    use HasBaseService;

    public function __construct(
        MessageRepositoryInterface $messageRepository
    )
    {
        $this->repository = $messageRepository;
    }

    public function sendMessage(string $phoneNumber, string $message): Model
    {
        $data = [
            'phone_number' => $phoneNumber,
            'message' => $message,
            'state' => \App\Enums\MessageState::Pending,
            'sent_at' => now(),
        ];

        return $this->create($data);
    }

    public function markAsSent(int $id): bool
    {
        return $this->update($id, [
            'state' => \App\Enums\MessageState::Sent,
        ]);
    }

    public function getMessagesByState(\App\Enums\MessageState $state): Collection
    {
        return $this->repository->getByState($state);
    }

    public function getPendingMessages(): Collection
    {
        return $this->getMessagesByState(\App\Enums\MessageState::Pending);
    }
}
