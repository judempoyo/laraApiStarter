<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// Helpers to resolve generated file paths
function generatedPath(string $relative): string
{
    return app_path($relative);
}

function assertFileGeneratedAndClean(string $path): void
{
    expect(File::exists($path))->toBeTrue("Expected file to be created: {$path}");

    $content = File::get($path);

    // No unresolved placeholders should remain
    expect($content)
        ->not->toContain('{{ ')
        ->not->toContain('}}');

    // Must have strict types
    expect($content)->toContain('declare(strict_types=1)');
}

function deleteIfExists(string ...$paths): void
{
    foreach ($paths as $path) {
        if (File::exists($path)) {
            File::delete($path);
        }
        $dir = dirname($path);
        if (File::isDirectory($dir) && count(File::files($dir)) === 0) {
            File::deleteDirectory($dir);
        }
    }
}

// ─── make:api-dto ────────────────────────────────────────────────────────────────

describe('make:api-dto', function () {
    afterEach(function () {
        deleteIfExists(generatedPath('DTOs/Test/TestDTO.php'));
    });

    it('generates a DTO with correct namespace and placeholders', function () {
        $path = generatedPath('DTOs/Test/TestDTO.php');

        Artisan::call('make:api-dto', ['name' => 'Test/TestDTO']);

        assertFileGeneratedAndClean($path);

        $content = File::get($path);
        expect($content)
            ->toContain('namespace App\\DTOs\\Test')
            ->toContain('class TestDTO')
            ->toContain('static function fromRequest');
    });

    it('fails with a clear error when name is empty', function () {
        // GeneratorCommand requires name — it will throw or return non-zero
        $exitCode = Artisan::call('make:api-dto', ['name' => '']);
        expect($exitCode)->toBe(1); // failure
    });

    it('does not overwrite an existing file without --force', function () {
        $path = generatedPath('DTOs/Test/TestDTO.php');

        Artisan::call('make:api-dto', ['name' => 'Test/TestDTO']);
        $firstModified = filemtime($path);
        sleep(1);

        Artisan::call('make:api-dto', ['name' => 'Test/TestDTO']);
        $secondModified = filemtime($path);

        expect($firstModified)->toBe($secondModified);
    });

    it('overwrites an existing file with --force', function () {
        $path = generatedPath('DTOs/Test/TestDTO.php');

        Artisan::call('make:api-dto', ['name' => 'Test/TestDTO']);
        $firstModified = filemtime($path);
        sleep(1);

        Artisan::call('make:api-dto', ['name' => 'Test/TestDTO', '--force' => true]);
        $secondModified = filemtime($path);

        expect($secondModified)->toBeGreaterThan($firstModified);
    });
});

// ─── make:api-action ─────────────────────────────────────────────────────────────

describe('make:api-action', function () {
    afterEach(function () {
        deleteIfExists(generatedPath('Actions/Test/TestAction.php'));
    });

    it('generates an Action with execute() method', function () {
        $path = generatedPath('Actions/Test/TestAction.php');

        Artisan::call('make:api-action', ['name' => 'Test/TestAction']);

        assertFileGeneratedAndClean($path);

        $content = File::get($path);
        expect($content)
            ->toContain('namespace App\\Actions\\Test')
            ->toContain('class TestAction')
            ->toContain('public function execute(');
    });

    it('wires the DTO import when --dto is provided', function () {
        $path = generatedPath('Actions/Test/TestAction.php');

        Artisan::call('make:api-action', [
            'name' => 'Test/TestAction',
            '--dto' => 'Test/TestDTO',
        ]);

        $content = File::get($path);
        expect($content)
            ->toContain('use App\\DTOs\\Test\\TestDTO')
            ->toContain('TestDTO $dto');
    });

    it('fails with a clear error when name is empty', function () {
        $exitCode = Artisan::call('make:api-action', ['name' => '']);
        expect($exitCode)->toBe(1);
    });
});

// ─── make:api-request ────────────────────────────────────────────────────────

describe('make:api-request', function () {
    afterEach(function () {
        deleteIfExists(generatedPath('Http/Requests/Test/TestRequest.php'));
    });

    it('generates a Request extending ApiRequest', function () {
        $path = generatedPath('Http/Requests/Test/TestRequest.php');

        Artisan::call('make:api-request', ['name' => 'Test/TestRequest']);

        assertFileGeneratedAndClean($path);

        $content = File::get($path);
        expect($content)
            ->toContain('namespace App\\Http\\Requests\\Test')
            ->toContain('extends ApiRequest')
            ->toContain('public function authorize(): bool')
            ->toContain('public function rules(): array')
            ->toContain('public function messages(): array');
    });

    it('fails with a clear error when name is empty', function () {
        $exitCode = Artisan::call('make:api-request', ['name' => '']);
        expect($exitCode)->toBe(1);
    });
});

// ─── make:api-resource ───────────────────────────────────────────────────────

describe('make:api-resource', function () {
    afterEach(function () {
        deleteIfExists(generatedPath('Http/Resources/TestResource.php'));
    });

    it('generates a Resource extending JsonResource', function () {
        $path = generatedPath('Http/Resources/TestResource.php');

        Artisan::call('make:api-resource', ['name' => 'TestResource']);

        assertFileGeneratedAndClean($path);

        $content = File::get($path);
        expect($content)
            ->toContain('namespace App\\Http\\Resources')
            ->toContain('extends JsonResource')
            ->toContain('public function toArray(Request $request): array');
    });

    it('fails with a clear error when name is empty', function () {
        $exitCode = Artisan::call('make:api-resource', ['name' => '']);
        expect($exitCode)->toBe(1);
    });
});

// ─── make:api-controller ─────────────────────────────────────────────────────

describe('make:api-controller', function () {
    afterEach(function () {
        deleteIfExists(generatedPath('Http/Controllers/Api/V1/TestController.php'));
    });

    it('generates a Controller with CRUD methods in the v1 namespace', function () {
        $path = generatedPath('Http/Controllers/Api/V1/TestController.php');

        Artisan::call('make:api-controller', [
            'name' => 'TestController',
            '--entity' => 'Test',
        ]);

        assertFileGeneratedAndClean($path);

        $content = File::get($path);
        expect($content)
            ->toContain('namespace App\\Http\\Controllers\\Api\\V1')
            ->toContain('class TestController')
            ->toContain('extends Controller')
            ->toContain('public function index()')
            ->toContain('public function store(')
            ->toContain('public function show(')
            ->toContain('public function update(')
            ->toContain('public function destroy(');
    });

    it('resolves entity name from controller name when --entity is omitted', function () {
        $path = generatedPath('Http/Controllers/Api/V1/TestController.php');

        Artisan::call('make:api-controller', ['name' => 'TestController']);

        $content = File::get($path);
        // imports should reference "Test" entity
        expect($content)->toContain('CreateTestAction');
    });

    it('fails with a clear error when name is empty', function () {
        $exitCode = Artisan::call('make:api-controller', ['name' => '']);
        expect($exitCode)->toBe(1);
    });
});

// ─── make:api-stack ─────────────────────────────────────────────────

describe('make:api-stack', function () {
    $entity = 'Dummy';

    $allFiles = fn (string $e): array => [
        generatedPath("Actions/{$e}/{$e}Action.php"),
        generatedPath("DTOs/{$e}/{$e}DTO.php"),
        generatedPath("Http/Requests/{$e}/{$e}Request.php"),
        generatedPath("Http/Resources/{$e}Resource.php"),
        generatedPath("Http/Controllers/Api/V1/{$e}Controller.php"),
    ];

    $crudFiles = fn (string $e): array => [
        generatedPath("Actions/{$e}/Create{$e}Action.php"),
        generatedPath("Actions/{$e}/Update{$e}Action.php"),
        generatedPath("Actions/{$e}/Delete{$e}Action.php"),
        generatedPath("DTOs/{$e}/{$e}StoreDTO.php"),
        generatedPath("DTOs/{$e}/{$e}UpdateDTO.php"),
        generatedPath("Http/Requests/{$e}/{$e}StoreRequest.php"),
        generatedPath("Http/Requests/{$e}/{$e}UpdateRequest.php"),
        generatedPath("Http/Resources/{$e}Resource.php"),
        generatedPath("Http/Controllers/Api/V1/{$e}Controller.php"),
    ];

    afterEach(function () use ($entity, $allFiles, $crudFiles) {
        deleteIfExists(...$allFiles($entity));
        deleteIfExists(...$crudFiles($entity));
    });

    it('generates 5 files in simple mode', function () use ($entity, $allFiles) {
        Artisan::call('make:api-stack', ['name' => $entity]);

        foreach ($allFiles($entity) as $path) {
            assertFileGeneratedAndClean($path);
        }
    });

    it('generates 9 files in --crud mode', function () use ($entity, $crudFiles) {
        Artisan::call('make:api-stack', ['name' => $entity, '--crud' => true]);

        foreach ($crudFiles($entity) as $path) {
            assertFileGeneratedAndClean($path);
        }
    });

    it('fails with a clear error when name is invalid', function () {
        $exitCode = Artisan::call('make:api-stack', ['name' => '123invalid!']);
        expect($exitCode)->toBe(1);
    });
});

it('generates a scaffold controller that loads through PSR-4 on Linux', function (): void {
    $entity = 'ScaffoldAutoloadProbe';
    $paths = [
        "Http/Controllers/Api/V1/{$entity}Controller.php", "Actions/{$entity}", "DTOs/{$entity}",
        "Http/Requests/{$entity}", "Http/Resources/{$entity}Resource.php",
    ];
    try {
        expect(Artisan::call('make:api-scaffold', ['entity' => $entity, '--no-model' => true, '--no-migration' => true]))->toBe(0);
        expect(class_exists('App\\Http\\Controllers\\Api\\V1\\'.$entity.'Controller'))->toBeTrue();
    } finally {
        foreach ($paths as $path) {
            File::isDirectory(app_path($path)) ? File::deleteDirectory(app_path($path)) : File::delete(app_path($path));
        }
    }
});
