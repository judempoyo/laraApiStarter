<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use App\Console\Support\ModelIntrospector;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'las:resource')]
class MakeApiResourceCommand extends GeneratorCommand
{
    protected $name = 'las:resource';

    protected $description = 'Create a new API Resource class (JSON transformer for Eloquent models)';

    protected $type = 'Resource';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->components->error('The name argument is required. Usage: php artisan las:resource ProductResource');
            return self::FAILURE;
        }

        return (int) parent::handle();
    }

    protected function getStub(): string
    {
        return base_path(config('api-generator.paths.stub', 'stubs') . '/resource.api.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return config('api-generator.namespaces.resource', $rootNamespace . '\\Http\\Resources');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $modelOption = $this->option('model');
        $modelClass  = $modelOption ? ModelIntrospector::resolveModelClass($modelOption) : null;

        if ($modelClass) {
            $introspector = new ModelIntrospector($modelClass);
            $stub         = str_replace('{{ fields }}', $introspector->generateResourceFields(), $stub);
        } else {
            $stub = str_replace('{{ fields }}', "            'id' => \$this->id,", $stub);
        }

        return $stub;
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the Resource class (e.g. ProductResource)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Introspect this model to generate resource fields automatically (e.g. --model=Product)'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the class if it already exists'],
        ];
    }
}
