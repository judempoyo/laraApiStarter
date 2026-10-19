<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Modules\ModuleManager;

class AddModuleCommand extends ModuleCommand
{
    protected $signature = 'api:add {module* : Modules to install, including their dependencies}';

    protected $description = 'Install optional API modules';

    public function handle(ModuleManager $manager): int
    {
        return $this->safely(fn () => $this->installModules($manager, $this->argument('module')));
    }
}
