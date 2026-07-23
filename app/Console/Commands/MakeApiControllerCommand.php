<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'las:controller')]
class MakeApiControllerCommand extends GeneratorCommand
{
    protected $name = 'las:controller';

    protected $description = 'Create an API Controller with CRUD methods wired to Actions, DTOs and Resources';

    protected $type = 'Controller';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->components->error('The name argument is required. Usage: php artisan las:controller ProductController --entity=Product');
            return self::FAILURE;
        }

        return (int) parent::handle();
    }

    protected function getStub(): string
    {
        return base_path(config('api-generator.paths.stub', 'stubs') . '/controller.api.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return config('api-generator.namespaces.controller', $rootNamespace . '\\Http\\Controllers\\Api\\v1');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        // Derive entity name: explicit option, or strip "Controller" suffix from class basename
        $baseName = class_basename($name);
        $entity   = $this->option('entity') ?? preg_replace('/Controller$/i', '', $baseName);

        if (empty($entity)) {
            $entity = $baseName;
        }

        $modelNamespace    = config('api-generator.namespaces.model', 'App\\Models');
        $dtoNamespace      = config('api-generator.namespaces.dto', 'App\\DTOs');
        $actionNamespace   = config('api-generator.namespaces.action', 'App\\Actions');
        $requestNamespace  = config('api-generator.namespaces.request', 'App\\Http\\Requests');
        $resourceNamespace = config('api-generator.namespaces.resource', 'App\\Http\\Resources');

        $modelVariable = lcfirst($entity);
        // "ProductCategory" → "Product Category"
        $modelLabel = preg_replace('/(?<!^)[A-Z]/', ' $0', $entity) ?? $entity;

        $imports = [
            "use {$modelNamespace}\\{$entity};",
            "use {$resourceNamespace}\\{$entity}Resource;",
            "use {$actionNamespace}\\{$entity}\\Create{$entity}Action;",
            "use {$actionNamespace}\\{$entity}\\Update{$entity}Action;",
            "use {$actionNamespace}\\{$entity}\\Delete{$entity}Action;",
            "use {$dtoNamespace}\\{$entity}\\{$entity}StoreDTO;",
            "use {$dtoNamespace}\\{$entity}\\{$entity}UpdateDTO;",
            "use {$requestNamespace}\\{$entity}\\{$entity}StoreRequest;",
            "use {$requestNamespace}\\{$entity}\\{$entity}UpdateRequest;",
        ];

        $replacements = [
            '{{ imports }}'            => implode("\n", $imports),
            '{{ modelClass }}'         => $entity,
            '{{ modelVariable }}'      => $modelVariable,
            '{{ modelLabel }}'         => $modelLabel,
            '{{ resourceClass }}'      => $entity . 'Resource',
            '{{ storeActionClass }}'   => 'Create' . $entity . 'Action',
            '{{ updateActionClass }}'  => 'Update' . $entity . 'Action',
            '{{ deleteActionClass }}'  => 'Delete' . $entity . 'Action',
            '{{ storeDtoClass }}'      => $entity . 'StoreDTO',
            '{{ updateDtoClass }}'     => $entity . 'UpdateDTO',
            '{{ storeRequestClass }}'  => $entity . 'StoreRequest',
            '{{ updateRequestClass }}' => $entity . 'UpdateRequest',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the Controller class (e.g. ProductController)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['entity', 'e', InputOption::VALUE_OPTIONAL, 'The entity name this controller manages (defaults to controller name minus "Controller")'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the class if it already exists'],
        ];
    }
}
