<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallApiCommand extends Command
{
    protected $signature   = 'api:install';
    protected $description = 'Interactive setup for LaraApiStarter — configure auth driver, database, and run migrations.';

    public function handle(): int
    {
        $this->displayBanner();

        // ── Auth Driver ────────────────────────────────────────────────────
        $driver = $this->choice(
            'Which authentication driver do you want to use?',
            ['sanctum', 'passport'],
            0
        );

        $guard = $driver === 'passport' ? 'api' : 'sanctum';

        $this->writeEnvValue('AUTH_DRIVER', $driver);
        $this->writeEnvValue('AUTH_GUARD', $guard);

        if ($driver === 'passport') {
            $this->displayPassportInstructions();
        }

        // ── App Key ────────────────────────────────────────────────────────
        if ($this->confirm('Generate a new application key?', true)) {
            Artisan::call('key:generate', ['--ansi' => true]);
            $this->info('Application key generated.');
        }

        // ── Migrations ─────────────────────────────────────────────────────
        if ($this->confirm('Run database migrations now?', true)) {
            Artisan::call('migrate', ['--ansi' => true]);
            $this->info('Migrations completed.');
        }

        // ── Seeders ────────────────────────────────────────────────────────
        if ($this->confirm('Run database seeders?', false)) {
            Artisan::call('db:seed', ['--ansi' => true]);
            $this->info('Seeders completed.');
        }

        // ── Storage Link ───────────────────────────────────────────────────
        if ($this->confirm('Create the storage symlink?', true)) {
            Artisan::call('storage:link', ['--ansi' => true]);
            $this->info('Storage link created.');
        }

        $this->newLine();
        $this->displaySummary($driver, $guard);

        return self::SUCCESS;
    }

    private function displayBanner(): void
    {
        $this->newLine();
        $this->line('  <fg=blue>LaraApiStarter</> — Production API Starter');
        $this->line('  <fg=gray>github.com/judempoyo/lara-api-starter</>');
        $this->newLine();
    }

    private function displayPassportInstructions(): void
    {
        $this->newLine();
        $this->warn('Passport requires manual steps:');
        $this->line('  1. Add <comment>laravel/passport</comment> to composer.json and run <comment>composer update</comment>');
        $this->line('  2. In <comment>app/Models/User.php</comment>, replace:');
        $this->line('       <comment>use Laravel\Sanctum\HasApiTokens;</comment>');
        $this->line('     with:');
        $this->line('       <comment>use Laravel\Passport\HasApiTokens;</comment>');
        $this->line('  3. Run: <comment>php artisan passport:install</comment>');
        $this->line('  4. In <comment>config/auth.php</comment>, set the <comment>api</comment> guard driver to <comment>passport</comment>.');
        $this->newLine();
    }

    private function displaySummary(string $driver, string $guard): void
    {
        $this->line('  <fg=green>Setup complete!</>');
        $this->newLine();
        $this->line("  Auth driver : <comment>{$driver}</comment>");
        $this->line("  Auth guard  : <comment>{$guard}</comment>");
        $this->newLine();
        $this->line('  Next steps:');
        $this->line('    php artisan serve');
        $this->line('    Visit /api/v1/health to verify the installation');
        $this->line('    Visit /docs/api for the interactive API documentation');
        $this->newLine();
    }

    /**
     * Write or update a key-value pair in the .env file.
     */
    private function writeEnvValue(string $key, string $value): void
    {
        $path    = base_path('.env');
        $content = file_get_contents($path);

        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content .= PHP_EOL . "{$key}={$value}";
        }

        file_put_contents($path, $content);
    }
}
