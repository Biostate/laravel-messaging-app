<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        Campaign::factory()
            ->count(10)
            ->draft()
            ->create();

        Campaign::factory()
            ->count(5)
            ->sending()
            ->create();

        Campaign::factory()
            ->count(8)
            ->completed()
            ->create();

        Campaign::factory()
            ->count(2)
            ->failed()
            ->create();

        Campaign::factory()
            ->count(3)
            ->state(['name' => null])
            ->create();
    }
}
