<?php

namespace App\Services\Contracts;

use App\Enums\MessageState;
use Illuminate\Database\Eloquent\Collection;

interface MessageServiceInterface extends BaseServiceInterface
{
    public function sendMessage(string $phoneNumber, string $message): \Illuminate\Database\Eloquent\Model;

    public function markAsSent(int $id): bool;

    public function getMessagesByState(MessageState $state): Collection;

    public function getPendingMessages(): Collection;
}
