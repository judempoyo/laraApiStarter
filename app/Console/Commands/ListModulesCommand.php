<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;
use Illuminate\Console\Command;

class ListModulesCommand extends Command
{
    protected $signature = 'api:list';

    protected $description = 'List available and installed API modules';

    public function handle(ModuleManager $manager): int
    {
        $state = $manager->state();
        $rows = [];
        foreach ($manager->catalog() as $name => $manifest) {
            $rows[] = [$name, isset($state['modules'][$name]) ? 'installed' : 'available', implode(', ', $manifest['requires']), $manifest['description']];
        }
        $this->table(['Module', 'Status', 'Requires', 'Description'], $rows);

        return self::SUCCESS;
    }
}
