<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class InstallApiCommand extends ModuleCommand
{
    protected $signature = 'api:install
        {--preset= : minimal, standard or full}
        {--modules= : Additional comma-separated modules}
        {--database= : sqlite, mysql or pgsql}
        {--migrate : Run pending migrations}
        {--seed : Run installed module seeders}
        {--storage-link : Create the public storage link}
        {--ai : Launch the interactive Boost agent installer}';

    protected $description = 'Install the Sanctum API core and selected optional modules';

    public function handle(ModuleManager $manager): int
    {
        return $this->safely(function () use ($manager): void {
            $interactive = $this->input->isInteractive();
            $this->line('LaraApiStarter — jump/lara-api-starter');
            $presets = config('api-starter.presets');
            $preset = $this->option('preset') ?: ($interactive ? select('Choose a preset', array_keys($presets), 'standard') : 'minimal');
            if (! array_key_exists($preset, $presets)) {
                throw new \InvalidArgumentException('Unknown preset: '.$preset);
            }
            $database = $this->option('database') ?: ($interactive ? select('Database', ['sqlite', 'mysql', 'pgsql'], 'sqlite') : null);
            if ($database !== null && ! in_array($database, ['sqlite', 'mysql', 'pgsql'], true)) {
                throw new \InvalidArgumentException('Unsupported database driver.');
            }
            $modules = array_values(array_filter(explode(',', (string) $this->option('modules'))));
            $modules = array_unique([...$presets[$preset], ...$modules]);
            if ($interactive) {
                $options = [];
                foreach ($manager->catalog() as $name => $manifest) {
                    $options[$name] = $name.' — '.$manifest['description'];
                }
                $modules = multiselect('Choose optional modules', $options, $modules);
            }
            $resolved = array_keys($manager->resolve($modules));
            $dependencies = array_diff($resolved, $modules);
            if ($dependencies !== []) {
                $this->line('Required dependencies included: '.implode(', ', $dependencies));
            }
            if (! File::exists(base_path('.env'))) {
                File::copy(base_path('.env.example'), base_path('.env'));
            }
            if ($database !== null) {
                $this->writeEnv('DB_CONNECTION', $database);
                if ($database === 'sqlite') {
                    $this->writeEnv('DB_DATABASE', 'database/database.sqlite');
                    if (! File::exists(database_path('database.sqlite'))) {
                        File::put(database_path('database.sqlite'), '');
                    }
                } else {
                    $this->line('Configure DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME and DB_PASSWORD in .env before migrating.');
                }
            }
            $env = File::get(base_path('.env'));
            if (! preg_match('/^APP_KEY=.+$/m', $env)) {
                $this->runArtisan(['key:generate', '--force']);
            }
            $this->installModules($manager, $resolved);
            if ($this->option('migrate') || ($interactive && confirm('Run pending migrations?', true))) {
                $this->runArtisan(['migrate', '--force']);
            }
            if ($this->option('seed') || ($interactive && confirm('Run installed module seeders?', false))) {
                $this->runArtisan(['db:seed', '--force']);
            }
            if ($this->option('storage-link') || ($interactive && confirm('Create the storage link?', true))) {
                if (! is_link(public_path('storage'))) {
                    $this->runArtisan(['storage:link']);
                }
            }
            if ($this->option('ai') || ($interactive && confirm('Configure AI agents with Laravel Boost?', true))) {
                if (! $interactive) {
                    throw new \RuntimeException('Boost 2.1 selects agents interactively. Run php artisan boost:install in a terminal.');
                }
                // Boost owns agent selection and generated AGENTS.md / CLAUDE.md files.
                $this->call('boost:install');
            }
            $this->info('Installed '.$preset.'. Health: /api/'.strtolower(config('api.version')).'/health; docs: /docs/api');
        });
    }

    private function runArtisan(array $arguments): void
    {
        $process = new Process([PHP_BINARY, 'artisan', ...$arguments, '--no-interaction'], base_path());
        $process->setTimeout(180);
        $process->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    }

    private function writeEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        $content = File::get($path);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        $line = $key.'='.$value;
        $content = preg_match($pattern, $content) ? preg_replace_callback($pattern, fn () => $line, $content) : $content."\n".$line."\n";
        File::put($path, $content);
    }
}
