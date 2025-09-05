<?php

// app/Repositories/Contracts/BaseRepositoryInterface.php

namespace App\Repositories\Contracts;

use App\Enums\MessageState;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{
    public function getByState(MessageState $state): Collection;
}
