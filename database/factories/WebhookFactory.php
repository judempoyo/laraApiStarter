<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'url'         => $this->faker->url(),
            'events'      => ['api_key.created'],
            'secret'      => Str::random(40),
            'is_active'   => true,
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
