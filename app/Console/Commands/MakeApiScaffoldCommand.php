<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeApiScaffoldCommand extends Command
{
    protected $signature   = 'make:api-scaffold {entity : The model name (e.g. Product)}
                              {--no-model : Skip model generation}
                              {--no-migration : Skip migration generation}';

    protected $description = 'Generate a full API scaffold: Model, Controller, Actions, DTOs, Requests, Resource, and a route comment.';

    private string $entityName;
    private string $entityPlural;
    private string $entitySnake;
    private string $entitySnakePlural;
    private string $entityCamel;

    public function handle(): int
    {
        $this->entityName        = Str::studly($this->argument('entity'));
        $this->entityPlural      = Str::pluralStudly($this->entityName);
        $this->entitySnake       = Str::snake($this->entityName);
        $this->entitySnakePlural = Str::snake($this->entityPlural);
        $this->entityCamel       = Str::camel($this->entityName);

        $this->line("Generating API scaffold for <comment>{$this->entityName}</comment>...");
        $this->newLine();

        if (! $this->option('no-model')) {
            $this->generateModel();
        }

        if (! $this->option('no-migration')) {
            $this->call('make:migration', [
                'name' => "create_{$this->entitySnakePlural}_table",
            ]);
        }

        $this->generateController();
        $this->generateActions();
        $this->generateDTOs();
        $this->generateRequests();
        $this->generateResource();
        $this->displayRouteHint();

        $this->newLine();
        $this->info('Scaffold generated successfully.');

        return self::SUCCESS;
    }

    private function generateModel(): void
    {
        $path = app_path("Models/{$this->entityName}.php");

        if (file_exists($path)) {
            $this->warn("  Model already exists — skipped: app/Models/{$this->entityName}.php");
            return;
        }

        $n = $this->entityName;

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Model;

class {$n} extends Model
{
    /** @use HasFactory<\\Database\\Factories\\{$n}Factory> */
    use HasFactory;

    protected \$fillable = [
        // TODO: add fillable columns
    ];
}
PHP;

        $this->writeFile($path, $content, "app/Models/{$this->entityName}.php");
    }

    private function generateController(): void
    {
        $dir  = app_path('Http/Controllers/Api/v1');
        $path = "{$dir}/{$this->entityName}Controller.php";

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($path)) {
            $this->warn("  Controller already exists — skipped: app/Http/Controllers/Api/v1/{$this->entityName}Controller.php");
            return;
        }

        $n  = $this->entityName;
        $ns = $this->entitySnake;
        $np = $this->entitySnakePlural;
        $nc = $this->entityCamel;

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api\\v1;

use App\\Actions\\{$n}\\Create{$n}Action;
use App\\Actions\\{$n}\\Delete{$n}Action;
use App\\Actions\\{$n}\\Update{$n}Action;
use App\\DTOs\\{$n}\\Create{$n}DTO;
use App\\DTOs\\{$n}\\Update{$n}DTO;
use App\\Http\\Controllers\\Controller;
use App\\Http\\Requests\\{$n}\\Store{$n}Request;
use App\\Http\\Requests\\{$n}\\Update{$n}Request;
use App\\Http\\Resources\\{$n}Resource;
use App\\Http\\Responses\\ApiResponse;
use App\\Models\\{$n};
use Illuminate\\Http\\JsonResponse;

class {$n}Controller extends Controller
{
    /**
     * Display a listing of {$np}.
     */
    public function index(): JsonResponse
    {
        return ApiResponse::paginated({$n}::paginate(config('api.pagination.default_per_page', 15)));
    }

    /**
     * Store a newly created {$ns}.
     */
    public function store(Store{$n}Request \$request, Create{$n}Action \$action): JsonResponse
    {
        \$result = \$action->execute(
            Create{$n}DTO::fromRequest(\$request->validated())
        );

        return ApiResponse::created(
            {$n}Resource::make(\$result),
            '{$n} created successfully.'
        );
    }

    /**
     * Display the specified {$ns}.
     */
    public function show({$n} \${$nc}): JsonResponse
    {
        return ApiResponse::success(
            {$n}Resource::make(\${$nc}),
            '{$n} retrieved successfully.'
        );
    }

    /**
     * Update the specified {$ns}.
     */
    public function update(Update{$n}Request \$request, {$n} \${$nc}, Update{$n}Action \$action): JsonResponse
    {
        \$result = \$action->execute(
            \${$nc},
            Update{$n}DTO::fromRequest(\$request->validated())
        );

        return ApiResponse::success(
            {$n}Resource::make(\$result),
            '{$n} updated successfully.'
        );
    }

    /**
     * Remove the specified {$ns}.
     */
    public function destroy({$n} \${$nc}, Delete{$n}Action \$action): JsonResponse
    {
        \$action->execute(\${$nc});

        return ApiResponse::noContent('{$n} deleted successfully.');
    }
}
PHP;

        $this->writeFile($path, $content, "app/Http/Controllers/Api/v1/{$this->entityName}Controller.php");
    }

    private function generateActions(): void
    {
        $actionsDir = app_path("Actions/{$this->entityName}");

        if (! is_dir($actionsDir)) {
            mkdir($actionsDir, 0755, true);
        }

        foreach (['Create', 'Update', 'Delete'] as $prefix) {
            $filename = "{$prefix}{$this->entityName}Action.php";
            $path     = "{$actionsDir}/{$filename}";

            if (file_exists($path)) {
                $this->warn("  Action already exists — skipped: app/Actions/{$this->entityName}/{$filename}");
                continue;
            }

            $content = $prefix === 'Delete'
                ? $this->buildDeleteAction()
                : $this->buildCrudAction($prefix);

            $this->writeFile($path, $content, "app/Actions/{$this->entityName}/{$filename}");
        }
    }

    private function buildCrudAction(string $prefix): string
    {
        $n   = $this->entityName;
        $nc  = $this->entityCamel;
        $dto = $prefix === 'Create' ? "Create{$n}DTO" : "Update{$n}DTO";

        $modelParam = $prefix === 'Update' ? "\n        {$n} \${$nc}," : '';

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\Actions\\{$n};

use App\\DTOs\\{$n}\\{$dto};
use App\\Models\\{$n};

class {$prefix}{$n}Action
{
    public function execute({$modelParam}
        {$dto} \$dto
    ): {$n} {
        // TODO: implement
        return new {$n}();
    }
}
PHP;
    }

    private function buildDeleteAction(): string
    {
        $n  = $this->entityName;
        $nc = $this->entityCamel;

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\\Actions\\{$n};

use App\\Models\\{$n};

class Delete{$n}Action
{
    public function execute({$n} \${$nc}): void
    {
        \${$nc}->delete();
    }
}
PHP;
    }

    private function generateDTOs(): void
    {
        $dtoDir = app_path("DTOs/{$this->entityName}");

        if (! is_dir($dtoDir)) {
            mkdir($dtoDir, 0755, true);
        }

        $n = $this->entityName;

        foreach (['Create', 'Update'] as $prefix) {
            $filename = "{$prefix}{$n}DTO.php";
            $path     = "{$dtoDir}/{$filename}";

            if (file_exists($path)) {
                $this->warn("  DTO already exists — skipped: app/DTOs/{$n}/{$filename}");
                continue;
            }

            $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\DTOs\\{$n};

class {$prefix}{$n}DTO
{
    public function __construct(
        // TODO: add typed properties
    ) {}

    public static function fromRequest(array \$data): self
    {
        return new self(
            // TODO: map \$data to constructor args
        );
    }
}
PHP;

            $this->writeFile($path, $content, "app/DTOs/{$n}/{$filename}");
        }
    }

    private function generateRequests(): void
    {
        $requestDir = app_path("Http/Requests/{$this->entityName}");

        if (! is_dir($requestDir)) {
            mkdir($requestDir, 0755, true);
        }

        $n = $this->entityName;

        foreach (['Store', 'Update'] as $prefix) {
            $filename = "{$prefix}{$n}Request.php";
            $path     = "{$requestDir}/{$filename}";

            if (file_exists($path)) {
                $this->warn("  Request already exists — skipped: app/Http/Requests/{$n}/{$filename}");
                continue;
            }

            $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Http\\Requests\\{$n};

use App\\Http\\Requests\\ApiRequest;

class {$prefix}{$n}Request extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: add validation rules
        ];
    }
}
PHP;

            $this->writeFile($path, $content, "app/Http/Requests/{$n}/{$filename}");
        }
    }

    private function generateResource(): void
    {
        $n    = $this->entityName;
        $path = app_path("Http/Resources/{$n}Resource.php");

        if (file_exists($path)) {
            $this->warn("  Resource already exists — skipped: app/Http/Resources/{$n}Resource.php");
            return;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\\Http\\Resources;

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$n}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
            'id'         => \$this->id,
            // TODO: add fields
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
PHP;

        $this->writeFile($path, $content, "app/Http/Resources/{$n}Resource.php");
    }

    private function displayRouteHint(): void
    {
        $this->newLine();
        $this->line('  <fg=yellow>Add to routes/api/user.php or routes/api/admin.php:</>');
        $this->newLine();
        $this->line("  <comment>Route::apiResource('{$this->entitySnakePlural}', \\App\\Http\\Controllers\\Api\\v1\\{$this->entityName}Controller::class);</comment>");
        $this->newLine();
    }

    private function writeFile(string $path, string $content, string $displayPath): void
    {
        file_put_contents($path, $content);
        $this->line("  <fg=green>Created</> {$displayPath}");
    }
}
