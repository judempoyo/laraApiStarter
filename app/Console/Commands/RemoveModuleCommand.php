<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;

class RemoveModuleCommand extends ModuleCommand
{
    protected $signature = 'api:remove {module : Installed module to remove}';

    protected $description = 'Remove module files and AI resources, preserving database data';

    public function handle(ModuleManager $manager): int
    {
        return $this->safely(function () use ($manager): void {
            $removed = $manager->remove($this->argument('module'));
            $this->info($removed === [] ? 'Module not installed.' : 'Removed: '.implode(', ', $removed));
            $this->refreshProject();
            $this->line('Database tables and Composer packages are retained. See UPGRADING.md for cleanup.');
        });
    }
}
