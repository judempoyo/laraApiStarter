<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'resource' => $this->faker->randomElement(['user_preferences', 'notifications']),
            'format'   => ExportFormat::CSV,
            'status'   => ExportStatus::PENDING,
            'media_id' => null,
            'filters'  => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => ExportStatus::COMPLETED]);
    }

    public function failed(): static
    {
        return $this->state([
            'status'        => ExportStatus::FAILED,
            'error_message' => 'Simulated failure.',
        ]);
    }
}
