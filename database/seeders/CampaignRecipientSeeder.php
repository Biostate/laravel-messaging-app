<?php

namespace Database\Seeders;

use App\Enums\CampaignRecipientStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use Illuminate\Database\Seeder;

class CampaignRecipientSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::all();
        $recipients = Recipient::all();

        if ($campaigns->isEmpty() || $recipients->isEmpty()) {
            $this->command->warn('No campaigns or recipients found please run CampaignSeeder and RecipientSeeder.');

            return;
        }

        foreach ($campaigns as $campaign) {
            $recipientCount = rand(5, 15);
            $selectedRecipients = $recipients->random($recipientCount);

            foreach ($selectedRecipients as $recipient) {
                $status = $this->getRandomStatus();

                CampaignRecipient::factory()
                    ->state([
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipient->id,
                        'status' => $status,
                    ])
                    ->create();
            }
        }

        CampaignRecipient::factory()
            ->count(20)
            ->pending()
            ->create();

        CampaignRecipient::factory()
            ->count(15)
            ->sent()
            ->create();

        CampaignRecipient::factory()
            ->count(5)
            ->failed()
            ->create();
    }

    private function getRandomStatus(): CampaignRecipientStatus
    {
        $statuses = [
            CampaignRecipientStatus::Pending,
            CampaignRecipientStatus::Sent,
            CampaignRecipientStatus::Failed,
        ];

        return $statuses[array_rand($statuses)];
    }
}
