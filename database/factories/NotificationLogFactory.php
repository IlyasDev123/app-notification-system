<?php

namespace Database\Factories;

use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationLog>
 */
class NotificationLogFactory extends Factory
{
    protected $model = NotificationLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => fake()->uuid(),
            'channel' => fake()->randomElement(['email', 'sms', 'push']),
            'provider' => fake()->randomElement(['mailgun', 'twilio', 'firebase']),
            'recipient' => fake()->safeEmail(),
            'status' => 'queued',
            'response' => null,
        ];
    }
}
