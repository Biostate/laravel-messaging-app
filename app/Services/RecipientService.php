<?php

namespace App\Services;

use App\Repositories\Contracts\RecipientRepositoryInterface;
use App\Services\Contracts\RecipientServiceInterface;
use App\Services\Traits\HasBaseService;
use Illuminate\Database\Eloquent\Model;

class RecipientService implements RecipientServiceInterface
{
    use HasBaseService;

    public function __construct(RecipientRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getByPhoneNumber(string $phoneNumber): ?Model
    {
        return $this->repository->getByPhoneNumber($phoneNumber);
    }

    public function createOrUpdateByPhoneNumber(string $phoneNumber, array $data): Model
    {
        $recipient = $this->getByPhoneNumber($phoneNumber);

        if ($recipient) {
            $this->update($recipient->id, $data);

            return $this->getById($recipient->id);
        }

        return $this->create(array_merge($data, ['phone_number' => $phoneNumber]));
    }
}
