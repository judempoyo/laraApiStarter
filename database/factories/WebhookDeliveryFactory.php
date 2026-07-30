<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebhookStatus;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Factories\Factory;

class WebhookDeliveryFactory extends Factory
{
    protected $model = WebhookDelivery::class;

    public function definition(): array
    {
        return [
            'webhook_id'    => Webhook::factory(),
            'event'         => 'api_key.created',
            'payload'       => ['api_key_id' => $this->faker->randomNumber()],
            'status'        => WebhookStatus::SUCCESS,
            'response_code' => 200,
            'response_body' => 'OK',
            'attempt'       => 1,
            'delivered_at'  => now(),
        ];
    }
}
