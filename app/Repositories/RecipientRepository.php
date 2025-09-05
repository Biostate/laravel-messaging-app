<?php

namespace App\Repositories;

use App\Models\Recipient;
use App\Repositories\Contracts\RecipientRepositoryInterface;
use App\Repositories\Traits\HasBaseRepository;

class RecipientRepository implements RecipientRepositoryInterface
{
    use HasBaseRepository;

    public function __construct()
    {
        $this->model = Recipient::class;
    }

    public function getByPhoneNumber(string $phoneNumber): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->newModel()->query()->where('phone_number', $phoneNumber)->first();
    }
}
