<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

abstract class ModuleCommand extends Command
{
    protected function installModules(ModuleManager $manager, array $names): void
    {
        $packages = [];
        foreach ($manager->resolve($names) as $manifest) {
            $packages = array_merge($packages, $manifest['composer']);
        }
        $composer = json_decode(file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);
        $missing = array_diff_key($packages, $composer['require']);
        if ($missing !== []) {
            $arguments = ['composer', 'require', '--no-interaction', '--no-scripts'];
            foreach ($missing as $package => $version) {
                $arguments[] = $package.':'.$version;
            }
            $process = new Process($arguments, base_path());
            $process->setTimeout(300);
            $process->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
        }
        $added = $manager->add($names);
        $this->info($added === [] ? 'Modules already installed.' : 'Installed: '.implode(', ', $added));
        $this->refreshProject();
    }

    protected function refreshProject(): void
    {
        // A fresh process is required to discover newly copied providers and traits.
        $process = new Process(['composer', 'dump-autoload', '--no-interaction'], base_path());
        $process->setTimeout(120);
        $process->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
        foreach (['config:clear', 'route:clear', 'view:clear', 'event:clear'] as $command) {
            $this->call($command);
        }
        if (is_file(base_path('boost.json'))) {
            $process = new Process([PHP_BINARY, 'artisan', 'boost:update', '--no-interaction'], base_path());
            $process->setTimeout(120);
            $process->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
        }
    }

    protected function safely(callable $operation): int
    {
        try {
            $operation();

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
