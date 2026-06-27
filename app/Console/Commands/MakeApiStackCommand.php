<?php

declare (strict_types = 1);

namespace App\Console\Commands;

use App\Console\Support\ModelIntrospector;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'las:all')]
class MakeApiStackCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'las:all
        {name? : The entity name in PascalCase (e.g. Product, BlogPost)}
        {--crud : Generate full CRUD stack (Create/Update/Delete actions, Store/Update DTOs and Requests)}
        {--model= : Introspect this model for auto-filling properties, rules, and resource fields}
        {--force : Overwrite existing files without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a complete API resource stack (Actions, DTOs, Requests, Resource, Controller)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = $this->argument('name');

        if (empty($rawName)) {
            $rawName = $this->ask('What is the entity name? (e.g. Product, BlogPost)');
        }

        if (empty($rawName) || ! preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $rawName)) {
            $this->components->error('Invalid entity name. Use a simple PascalCase identifier (e.g. Product, BlogPost).');
            return self::FAILURE;
        }

        $entity    = ucfirst($rawName);
        $crud      = $this->option('crud');
        $force     = $this->option('force');
        $modelName = $this->option('model') ?? $entity;

        // Check if model exists for introspection
        $modelClass = ModelIntrospector::resolveModelClass($modelName);
        $modelArgs  = $modelClass ? ['--model' => $modelName] : [];
        $forceArgs  = $force ? ['--force' => true] : [];

        $this->newLine();
        $this->components->info("Generating API resource stack for [{$entity}]");

        if ($modelClass) {
            $this->components->twoColumnDetail('Model', $modelClass . ' (introspected)');
        } else {
            $this->components->twoColumnDetail('Model', 'None detected — using empty placeholders');
        }

        $this->components->twoColumnDetail('Mode', $crud ? 'Full CRUD' : 'Simple');
        $this->newLine();

        $generated = 0;
        $failed    = 0;

        if ($crud) {
            $this->writeSectionHeader('Actions');

            $generated += $this->generate('las:action', "{$entity}/Create{$entity}Action", array_merge(
                ['--dto' => "{$entity}/{$entity}StoreDTO"],
                $forceArgs
            ), $failed);

            $generated += $this->generate('las:action', "{$entity}/Update{$entity}Action", array_merge(
                ['--dto' => "{$entity}/{$entity}UpdateDTO"],
                $forceArgs
            ), $failed);

            $generated += $this->generate('las:action', "{$entity}/Delete{$entity}Action", $forceArgs, $failed);

            $this->writeSectionHeader('DTOs');
            $generated += $this->generate('las:dto', "{$entity}/{$entity}StoreDTO", array_merge($modelArgs, $forceArgs), $failed);
            $generated += $this->generate('las:dto', "{$entity}/{$entity}UpdateDTO", array_merge($modelArgs, $forceArgs), $failed);

            $this->writeSectionHeader('Form Requests');
            $generated += $this->generate('las:request', "{$entity}/{$entity}StoreRequest", array_merge($modelArgs, $forceArgs), $failed);
            $generated += $this->generate('las:request', "{$entity}/{$entity}UpdateRequest", array_merge($modelArgs, $forceArgs), $failed);
        } else {
            $this->writeSectionHeader('Action');
            $generated += $this->generate('las:action', "{$entity}/{$entity}Action", array_merge(
                ['--dto' => "{$entity}/{$entity}DTO"],
                $forceArgs
            ), $failed);

            $this->writeSectionHeader('DTO');
            $generated += $this->generate('las:dto', "{$entity}/{$entity}DTO", array_merge($modelArgs, $forceArgs), $failed);

            $this->writeSectionHeader('Form Request');
            $generated += $this->generate('las:request', "{$entity}/{$entity}Request", array_merge($modelArgs, $forceArgs), $failed);
        }

        $this->writeSectionHeader('Resource');
        $generated += $this->generate('las:resource', "{$entity}Resource", array_merge($modelArgs, $forceArgs), $failed);

        $this->writeSectionHeader('Controller');
        $generated += $this->generate('las:controller', "{$entity}Controller", array_merge(
            ['--entity' => $entity],
            $forceArgs
        ), $failed);

        // Summary
        $this->newLine();

        if ($failed > 0) {
            $this->components->warn("{$generated} file(s) created, {$failed} skipped (already exist or error).");
        } else {
            $this->components->info("{$generated} file(s) created successfully.");
        }

        $this->newLine();
        $this->line('  Next step — register your routes in <fg=cyan>routes/api.php</>:');
        $this->newLine();

        $plural = strtolower($entity) . 's';

        if ($crud) {
            $this->line("  Route::apiResource('{$plural}', \\App\\Http\\Controllers\\Api\\v1\\{$entity}Controller::class);");
        } else {
            $this->line("  Route::get('{$plural}', [\\App\\Http\\Controllers\\Api\\v1\\{$entity}Controller::class, 'index']);");
        }

        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Call a generator sub-command and track success/failure.
     */
    protected function generate(string $command, string $name, array $options, int &$failed): int
    {
        $exitCode = $this->call($command, array_merge(['name' => $name], $options));

        if ($exitCode !== self::SUCCESS) {
            $failed++;
            return 0;
        }

        return 1;
    }

    /**
     * Print a clean section separator.
     */
    protected function writeSectionHeader(string $title): void
    {
        $this->components->twoColumnDetail("<fg=yellow>{$title}</>", '');
    }
}
