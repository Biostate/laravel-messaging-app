<?php

namespace Database\Factories;

use App\Enums\CampaignRecipientStatus;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignRecipientFactory extends Factory
{
    protected $model = CampaignRecipient::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'recipient_id' => Recipient::factory(),
            'status' => CampaignRecipientStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CampaignRecipientStatus::Pending,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CampaignRecipientStatus::Sent,
            'sent_at' => now(),
            'message_id' => fake()->optional()->uuid(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => CampaignRecipientStatus::Failed,
            'failure_reason' => fake()->sentence(),
        ]);
    }
}
