<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CampaignManagementSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Campaign Management System...');

        $this->command->info('Creating Recipients...');
        $this->call(RecipientSeeder::class);

        $this->command->info('Creating Campaigns...');
        $this->call(CampaignSeeder::class);

        $this->command->info('Linking Campaigns to Recipients...');
        $this->call(CampaignRecipientSeeder::class);

        $this->command->info('Campaign Management System seeded successfully!');
        $this->command->info('   - Recipients: ' . \App\Models\Recipient::count());
        $this->command->info('   - Campaigns: ' . \App\Models\Campaign::count());
        $this->command->info('   - Campaign Recipients: ' . \App\Models\CampaignRecipient::count());
    }
}
