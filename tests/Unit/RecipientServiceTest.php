<?php

use App\Models\Recipient;
use App\Repositories\Contracts\RecipientRepositoryInterface;
use App\Services\RecipientService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->mockRepository = \Mockery::mock(RecipientRepositoryInterface::class);
    $this->service = new RecipientService($this->mockRepository);
});

describe('RecipientService', function () {

    describe('getAll() method', function () {
        it('returns all recipients from repository', function () {
            $recipients = Recipient::factory()->count(3)->create();
            $this->mockRepository->shouldReceive('all')->once()->andReturn($recipients);

            $result = $this->service->getAll();

            expect($result)->toBeInstanceOf(Collection::class);
            expect($result)->toHaveCount(3);
        });

        it('returns empty collection when no recipients exist', function () {
            $this->mockRepository->shouldReceive('all')->once()->andReturn(new Collection);

            $result = $this->service->getAll();

            expect($result)->toBeEmpty();
        });
    });

    describe('getById() method', function () {
        it('returns recipient when found', function () {
            $recipient = Recipient::factory()->create();
            $this->mockRepository->shouldReceive('find')->with($recipient->id)->once()->andReturn($recipient);

            $result = $this->service->getById($recipient->id);

            expect($result)->toBeInstanceOf(Recipient::class);
            expect($result->id)->toBe($recipient->id);
        });

        it('returns null when recipient not found', function () {
            $this->mockRepository->shouldReceive('find')->with(999)->once()->andReturn(null);

            $result = $this->service->getById(999);

            expect($result)->toBeNull();
        });
    });

    describe('create() method', function () {
        it('creates recipient with provided data', function () {
            $data = [
                'phone_number' => '+1234567890',
                'name' => 'John Doe',
            ];

            $createdRecipient = Recipient::factory()->make($data);
            $this->mockRepository->shouldReceive('create')->with($data)->once()->andReturn($createdRecipient);

            $result = $this->service->create($data);

            expect($result)->toBeInstanceOf(Recipient::class);
            expect($result->phone_number)->toBe('+1234567890');
        });
    });

    describe('update() method', function () {
        it('updates recipient with provided data', function () {
            $recipient = Recipient::factory()->create();
            $updateData = ['name' => 'Updated Name'];

            $this->mockRepository->shouldReceive('update')->with($recipient->id, $updateData)->once()->andReturn(true);

            $result = $this->service->update($recipient->id, $updateData);

            expect($result)->toBeTrue();
        });

        it('returns false when update fails', function () {
            $this->mockRepository->shouldReceive('update')->with(999, [])->once()->andReturn(false);

            $result = $this->service->update(999, []);

            expect($result)->toBeFalse();
        });
    });

    describe('delete() method', function () {
        it('deletes recipient successfully', function () {
            $recipient = Recipient::factory()->create();
            $this->mockRepository->shouldReceive('delete')->with($recipient->id)->once()->andReturn(true);

            $result = $this->service->delete($recipient->id);

            expect($result)->toBeTrue();
        });

        it('returns false when deletion fails', function () {
            $this->mockRepository->shouldReceive('delete')->with(999)->once()->andReturn(false);

            $result = $this->service->delete(999);

            expect($result)->toBeFalse();
        });
    });

    describe('getByPhoneNumber() method', function () {
        it('returns recipient when found by phone number', function () {
            $phoneNumber = '+1234567890';
            $recipient = Recipient::factory()->create(['phone_number' => $phoneNumber]);
            $this->mockRepository->shouldReceive('getByPhoneNumber')->with($phoneNumber)->once()->andReturn($recipient);

            $result = $this->service->getByPhoneNumber($phoneNumber);

            expect($result)->toBeInstanceOf(Recipient::class);
            expect($result->phone_number)->toBe($phoneNumber);
        });

        it('returns null when recipient not found by phone number', function () {
            $phoneNumber = '+9999999999';
            $this->mockRepository->shouldReceive('getByPhoneNumber')->with($phoneNumber)->once()->andReturn(null);

            $result = $this->service->getByPhoneNumber($phoneNumber);

            expect($result)->toBeNull();
        });
    });

    describe('createOrUpdateByPhoneNumber() method', function () {
        it('creates new recipient when not found', function () {
            $phoneNumber = '+1234567890';
            $data = ['name' => 'John Doe'];

            $this->mockRepository->shouldReceive('getByPhoneNumber')->with($phoneNumber)->once()->andReturn(null);

            $expectedCreateData = array_merge($data, ['phone_number' => $phoneNumber]);
            $createdRecipient = Recipient::factory()->make($expectedCreateData);
            $this->mockRepository->shouldReceive('create')->with($expectedCreateData)->once()->andReturn($createdRecipient);

            $result = $this->service->createOrUpdateByPhoneNumber($phoneNumber, $data);

            expect($result)->toBeInstanceOf(Recipient::class);
            expect($result->phone_number)->toBe($phoneNumber);
            expect($result->name)->toBe('John Doe');
        });

        it('updates existing recipient when found', function () {
            $phoneNumber = '+1234567890';
            $data = ['name' => 'Updated Name'];

            $existingRecipient = Recipient::factory()->create(['phone_number' => $phoneNumber]);
            $this->mockRepository->shouldReceive('getByPhoneNumber')->with($phoneNumber)->once()->andReturn($existingRecipient);

            $this->mockRepository->shouldReceive('update')->with($existingRecipient->id, $data)->once()->andReturn(true);

            $updatedRecipient = Recipient::factory()->make(array_merge($existingRecipient->toArray(), $data));
            $this->mockRepository->shouldReceive('find')->with($existingRecipient->id)->once()->andReturn($updatedRecipient);

            $result = $this->service->createOrUpdateByPhoneNumber($phoneNumber, $data);

            expect($result)->toBeInstanceOf(Recipient::class);
            expect($result->name)->toBe('Updated Name');
        });
    });

    describe('integration with real repository', function () {
        beforeEach(function () {
            $this->realRepository = new \App\Repositories\RecipientRepository;
            $this->realService = new RecipientService($this->realRepository);
        });

        it('works with real repository for complete flow', function () {
            $recipient = $this->realService->create([
                'phone_number' => '+1234567890',
                'name' => 'John Doe',
            ]);

            expect($recipient)->toBeInstanceOf(Recipient::class);
            expect($recipient->phone_number)->toBe('+1234567890');
            expect($recipient->name)->toBe('John Doe');

            $updated = $this->realService->update($recipient->id, ['name' => 'Jane Doe']);
            expect($updated)->toBeTrue();

            $updatedRecipient = $this->realService->getById($recipient->id);
            expect($updatedRecipient->name)->toBe('Jane Doe');
        });

        it('handles phone number lookups correctly', function () {
            $recipient = Recipient::factory()->create(['phone_number' => '+1234567890']);

            $foundRecipient = $this->realService->getByPhoneNumber('+1234567890');
            expect($foundRecipient)->toBeInstanceOf(Recipient::class);
            expect($foundRecipient->phone_number)->toBe('+1234567890');

            $notFoundRecipient = $this->realService->getByPhoneNumber('+9999999999');
            expect($notFoundRecipient)->toBeNull();
        });

        it('works with createOrUpdateByPhoneNumber', function () {
            $phoneNumber = '+1234567890';
            $data = ['name' => 'John Doe'];

            $recipient = $this->realService->createOrUpdateByPhoneNumber($phoneNumber, $data);
            expect($recipient)->toBeInstanceOf(Recipient::class);
            expect($recipient->phone_number)->toBe($phoneNumber);
            expect($recipient->name)->toBe('John Doe');

            $updatedData = ['name' => 'Jane Doe'];
            $updatedRecipient = $this->realService->createOrUpdateByPhoneNumber($phoneNumber, $updatedData);
            expect($updatedRecipient)->toBeInstanceOf(Recipient::class);
            expect($updatedRecipient->name)->toBe('Jane Doe');
        });
    });
});
