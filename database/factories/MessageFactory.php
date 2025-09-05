<?php

namespace Database\Factories;

use App\Enums\MessageState;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_number' => fake()->phoneNumber(),
            'message' => substr(fake()->sentence(), 0, 159),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => MessageState::Sent,
            'sent_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => MessageState::Pending,
        ]);
    }

    public function exceedsMessageLimit()
    {
        return $this->state(fn (array $attributes) => [
            'message' => str_repeat('a', 161),
        ]);
    }
}
