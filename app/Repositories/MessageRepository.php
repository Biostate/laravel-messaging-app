<?php

// app/Repositories/Eloquent/MessageRepository.php

namespace App\Repositories;

use App\Enums\MessageState;
use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Traits\HasBaseRepository;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    use HasBaseRepository;

    public function __construct()
    {
        $this->model = Message::class;
    }

    public function getByState(MessageState $state): Collection
    {
        return $this->newModel()->query()->where('state', $state)->get();
    }
}
