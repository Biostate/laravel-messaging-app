<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Seeder;

class RecipientSeeder extends Seeder
{
    public function run(): void
    {
        Recipient::factory()
            ->count(50)
            ->create();

        Recipient::factory()
            ->count(10)
            ->state(['name' => null])
            ->create();

        Recipient::factory()
            ->count(5)
            ->state(['phone_number' => '+1234567890'])
            ->create();
    }
}
