<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'las:action')]
class MakeActionCommand extends GeneratorCommand
{
    protected $name = 'las:action';

    protected $description = 'Create a new Action class';

    protected $type = 'Action';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->components->error('The name argument is required. Usage: php artisan las:action Product/CreateProductAction');
            return self::FAILURE;
        }

        return (int) parent::handle();
    }

    protected function getStub(): string
    {
        return base_path(config('api-generator.paths.stub', 'stubs') . '/action.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return config('api-generator.namespaces.action', $rootNamespace . '\\Actions');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $dto = $this->option('dto');

        if ($dto) {
            $dtoNamespace = config('api-generator.namespaces.dto', 'App\\DTOs');
            $dtoFqcn      = $dtoNamespace . '\\' . str_replace('/', '\\', $dto);
            $dtoBaseName  = class_basename($dtoFqcn);

            $stub = str_replace('{{ dtoImport }}', "use {$dtoFqcn};", $stub);
            $stub = str_replace('{{ dtoParameter }}', "{$dtoBaseName} \$dto", $stub);
        } else {
            $stub = str_replace('{{ dtoImport }}', '', $stub);
            $stub = str_replace('{{ dtoParameter }}', '', $stub);
        }

        // Collapse any triple blank lines left after placeholder removal
        return preg_replace("/\n{3,}/", "\n\n", $stub);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the Action class (e.g. Product/CreateProductAction)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['dto', 'd', InputOption::VALUE_OPTIONAL, 'The DTO this action receives (e.g. --dto=Product/ProductStoreDTO)'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the class if it already exists'],
        ];
    }
}
