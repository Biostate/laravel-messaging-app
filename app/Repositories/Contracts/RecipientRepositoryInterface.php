<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface RecipientRepositoryInterface extends BaseRepositoryInterface
{
    public function getByPhoneNumber(string $phoneNumber): ?Model;
}
