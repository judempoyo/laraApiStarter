<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DefaultUserSeeder::class);

        foreach (config('api-starter.seeders', []) as $seeder) {
            $this->call($seeder);
        }
    }
}
