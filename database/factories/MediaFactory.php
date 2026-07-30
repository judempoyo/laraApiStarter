<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'disk'          => 'local',
            'path'          => 'media/1/documents/' . Str::uuid() . '.pdf',
            'thumbnail_path' => null,
            'original_name' => $this->faker->word() . '.pdf',
            'mime_type'     => 'application/pdf',
            'size'          => $this->faker->numberBetween(1024, 1024 * 1024),
            'collection'    => 'documents',
        ];
    }

    public function image(): static
    {
        return $this->state([
            'path'          => 'media/1/images/' . Str::uuid() . '.jpg',
            'original_name' => 'photo.jpg',
            'mime_type'     => 'image/jpeg',
            'collection'    => 'images',
        ]);
    }
}
