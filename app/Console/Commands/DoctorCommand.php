<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Composer\InstalledVersions;
use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    protected $signature = 'api:doctor {--database : Also check pending migrations against the configured database}';

    protected $description = 'Check module files, dependencies, providers, traits and AI resources';

    public function handle(ModuleManager $manager): int
    {
        $issues = $manager->doctor();
        foreach ($manager->state()['modules'] as $name => $entry) {
            foreach ($manager->catalog()[$name]['composer'] ?? [] as $package => $constraint) {
                if (! InstalledVersions::isInstalled($package)) {
                    $issues[] = "{$name}: missing Composer package {$package}";
                }
            }
        }
        if ($this->option('database')) {
            $repository = app('migration.repository');
            $ran = $repository->repositoryExists() ? $repository->getRan() : [];
            foreach (glob(database_path('migrations/*.php')) ?: [] as $file) {
                if (! in_array(basename($file, '.php'), $ran, true)) {
                    $issues[] = 'Pending migration: '.basename($file);
                }
            }
        }
        foreach ($issues as $issue) {
            $this->error($issue);
        }
        if ($issues === []) {
            $this->info('API starter is consistent.');
        }

        return $issues === [] ? self::SUCCESS : self::FAILURE;
    }
}
