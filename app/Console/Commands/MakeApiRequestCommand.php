<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use App\Console\Support\ModelIntrospector;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'las:request')]
class MakeApiRequestCommand extends GeneratorCommand
{
    protected $name = 'las:request';

    protected $description = 'Create a new API Form Request class (extends ApiRequest with unified error formatting)';

    protected $type = 'Request';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->components->error('The name argument is required. Usage: php artisan las:request Product/ProductStoreRequest');
            return self::FAILURE;
        }

        return (int) parent::handle();
    }

    protected function getStub(): string
    {
        return base_path(config('api-generator.paths.stub', 'stubs') . '/request.api.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return config('api-generator.namespaces.request', $rootNamespace . '\\Http\\Requests');
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $modelOption = $this->option('model');
        $modelClass  = $modelOption ? ModelIntrospector::resolveModelClass($modelOption) : null;

        if ($modelClass) {
            $introspector = new ModelIntrospector($modelClass);
            $stub         = str_replace('{{ rules }}', $introspector->generateRequestRules(), $stub);
            $stub         = str_replace('{{ messages }}', $introspector->generateRequestMessages(), $stub);
        } else {
            $stub = str_replace('{{ rules }}', '            // TODO: define validation rules', $stub);
            $stub = str_replace('{{ messages }}', '            // TODO: define validation messages', $stub);
        }

        return $stub;
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the Request class (e.g. Product/ProductStoreRequest)'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Introspect this model to generate validation rules automatically (e.g. --model=Product)'],
            ['force', 'f', InputOption::VALUE_NONE, 'Overwrite the class if it already exists'],
        ];
    }
}
