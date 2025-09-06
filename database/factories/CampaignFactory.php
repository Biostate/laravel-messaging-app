<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'name' => fake()->optional()->sentence(3),
            'message' => $this->generateSmsMessage(),
            'status' => CampaignStatus::Draft,
        ];
    }

    private function generateSmsMessage(): string
    {
        $messages = [
            'Hello! This is a test message.',
            'Your order has been confirmed.',
            'Thank you for your purchase!',
            'Reminder: Your appointment is tomorrow.',
            'Welcome to our service!',
            'Your account has been activated.',
            'Payment received successfully.',
            'Your delivery is on the way.',
            'Please confirm your email address.',
            'Your subscription has been renewed.',
            'New message from support team.',
            'Your request has been processed.',
            'Thank you for contacting us.',
            'Your profile has been updated.',
            'Security alert: New login detected.',
        ];

        $baseMessage = fake()->randomElement($messages);

        if (strlen($baseMessage) <= 160) {
            return $baseMessage;
        }

        return substr($baseMessage, 0, 157).'...';
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Draft,
        ]);
    }

    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Sending,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Completed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Failed,
        ]);
    }
}
