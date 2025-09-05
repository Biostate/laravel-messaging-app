<?php

// app/Repositories/Eloquent/MessageRepository.php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Traits\HasBaseRepository;

class MessageRepository implements MessageRepositoryInterface
{
    use HasBaseRepository;

    public function __construct()
    {
        $this->model = Message::class;
    }
}
