<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface RecipientServiceInterface extends BaseServiceInterface
{
    public function getByPhoneNumber(string $phoneNumber): ?Model;

    public function createOrUpdateByPhoneNumber(string $phoneNumber, array $data): Model;
}
