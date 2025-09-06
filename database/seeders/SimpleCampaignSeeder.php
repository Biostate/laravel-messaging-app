<?php

namespace Database\Seeders;

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use Illuminate\Database\Seeder;

class SimpleCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $recipients = Recipient::factory()->count(5)->create();

        $this->command->info('Created 5 recipients');

        $campaign = Campaign::create([
            'name' => 'Test Campaign',
            'message' => 'Example text for SMS',
            'status' => CampaignStatus::Draft,
        ]);

        $this->command->info("Capaign created: {$campaign->id} - {$campaign->name}");

        foreach ($recipients as $recipient) {
            CampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'status' => CampaignRecipientStatus::Pending,
            ]);
        }

        $this->command->info("Created {$recipients->count()} campaign-recipient links");
        $this->command->info("Campaign {$campaign->id} is ready with {$recipients->count()} recipients");
    }
}
