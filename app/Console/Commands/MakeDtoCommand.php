<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use App\Console\Support\ModelIntrospector;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'las:dto')]
class MakeDtoCommand extends GeneratorCommand
{
    protected $name = 'las:dto';

    protected $description = 'Create a new DTO (Data Transfer Object) class';

    protected $type = 'DTO';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->components->error('The name argument is required. Usage: php artisan las:dto User/UserStoreDTO');
            return self::FAILURE;
        }

        return (int) parent::handle();
    }

    protected function getStub(): string
    {
        return base_path(config('api-generator.paths.stub', 'stubs') . '/dto.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return config('api-generator.namespaces.dto', $rootNamespace . '\\DTOs');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $modelOption = $this->option('model');
        $modelClass  = $modelOption ? ModelIntrospector::resolveModelClass($modelOption) : null;

        if ($modelClass) {
            $introspector = new ModelIntrospector($modelClass);
            $stub         = str_replace('{{ properties }}', $introspector->generateDtoProperties(), $stub);
            $stub         = str_replace('{{ fromRequestMapping }}', $introspector->generateFromRequestMapping(), $stub);
        } else {
            $stub = str_replace('{{ properties }}', '        // TODO: define your readonly properties', $stub);
            $stub = str_replace('{{ fromRequestMapping }}', '            // TODO: map validated fields', $stub);
        }

        return $stub;
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the DTO class (e.g. User/UserStoreDTO)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Introspect this model to generate properties automatically (e.g. --model=User)'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the class if it already exists'],
        ];
    }
}
