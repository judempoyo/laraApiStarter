<?php

declare(strict_types=1);

use App\Support\Modules\ModuleManager;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->root = sys_get_temp_dir().'/api-module-test-'.bin2hex(random_bytes(8));
    File::ensureDirectoryExists($this->root.'/app/Models');
    File::ensureDirectoryExists($this->root.'/bootstrap');
    File::put($this->root.'/app/Models/User.php', "<?php\nclass User {\n    // api-starter:traits\n}\n");
    File::put($this->root.'/bootstrap/providers.php', "<?php\nreturn [];\n");
    $this->manager = new ModuleManager($this->root);
    $this->fixture = function (string $name, array $requires = []): void {
        $dir = $this->root.'/stubs/modules/'.$name;
        File::ensureDirectoryExists($dir);
        File::put($dir.'/feature.php', '<?php // feature');
        File::put($dir.'/module.json', json_encode([
            'name' => $name,
            'version' => '1.0.0',
            'requires' => $requires,
            'composer' => [],
            'files' => ['feature.php' => 'app/'.$name.'.php'],
            'provider' => 'App\\Providers\\'.ucfirst($name).'ServiceProvider',
            'patches' => [['type' => 'user-trait', 'trait' => 'App\\Models\\Concerns\\Has'.ucfirst($name)]],
            'env' => [],
        ], JSON_THROW_ON_ERROR));
    };
});

afterEach(function (): void {
    File::deleteDirectory($this->root);
});

it('adds dependencies first and is idempotent', function (): void {
    ($this->fixture)('base');
    ($this->fixture)('feature', ['base']);
    expect($this->manager->add(['feature']))->toBe(['base', 'feature']);
    $state = File::get($this->root.'/.api-starter.json');
    expect($this->manager->add(['feature']))->toBe([]);
    expect(File::get($this->root.'/.api-starter.json'))->toBe($state);
    expect($this->manager->doctor())->toBe([]);
});

it('removes copied files providers and User patches', function (): void {
    ($this->fixture)('feature');
    $user = File::get($this->root.'/app/Models/User.php');
    $this->manager->add(['feature']);
    $this->manager->remove('feature');
    expect(File::exists($this->root.'/app/feature.php'))->toBeFalse();
    expect(File::get($this->root.'/app/Models/User.php'))->toBe($user);
    expect(File::get($this->root.'/bootstrap/providers.php'))->not->toContain('FeatureServiceProvider');
    expect($this->manager->state()['modules'])->toBe([]);
});

it('rejects missing dependencies without copying files', function (): void {
    ($this->fixture)('feature', ['missing']);
    expect(fn () => $this->manager->add(['feature']))->toThrow(RuntimeException::class, 'Unknown module');
    expect(File::exists($this->root.'/app/feature.php'))->toBeFalse();
});

it('rolls back earlier copies and patches after a later failure', function (): void {
    ($this->fixture)('base');
    ($this->fixture)('feature', ['base']);
    File::put($this->root.'/app/feature.php', 'developer content');
    $providers = File::get($this->root.'/bootstrap/providers.php');
    $user = File::get($this->root.'/app/Models/User.php');
    expect(fn () => $this->manager->add(['feature']))->toThrow(RuntimeException::class, 'overwrite');
    expect(File::exists($this->root.'/app/base.php'))->toBeFalse();
    expect(File::get($this->root.'/app/feature.php'))->toBe('developer content');
    expect(File::get($this->root.'/bootstrap/providers.php'))->toBe($providers);
    expect(File::get($this->root.'/app/Models/User.php'))->toBe($user);
    expect($this->manager->state()['modules'])->toBe([]);
});

it('preserves required modules and locally modified files', function (): void {
    ($this->fixture)('base');
    ($this->fixture)('feature', ['base']);
    $this->manager->add(['feature']);
    expect(fn () => $this->manager->remove('base'))->toThrow(RuntimeException::class, 'depends on');
    File::put($this->root.'/app/feature.php', 'local change');
    expect(fn () => $this->manager->remove('feature'))->toThrow(RuntimeException::class, 'Locally modified');
    expect(File::get($this->root.'/app/feature.php'))->toBe('local change');
});

it('rejects circular dependencies', function (): void {
    ($this->fixture)('one', ['two']);
    ($this->fixture)('two', ['one']);
    expect(fn () => $this->manager->add(['one']))->toThrow(RuntimeException::class, 'Circular');
});

it('installs and removes AI resources and managed environment values', function (): void {
    ($this->fixture)('feature');
    $path = $this->root.'/stubs/modules/feature/module.json';
    $manifest = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
    $manifest['files']['feature.md'] = '.ai/guidelines/modules/feature.md';
    $manifest['env']['FEATURE_MODE'] = ['default' => 'safe', 'comment' => 'Feature mode'];
    File::put($this->root.'/stubs/modules/feature/feature.md', '# Feature');
    File::put($path, json_encode($manifest, JSON_THROW_ON_ERROR));
    File::put($this->root.'/.env', "APP_NAME=API\n");
    $this->manager->add(['feature']);
    expect(File::get($this->root.'/.env'))->toContain('FEATURE_MODE=safe');
    expect(File::exists($this->root.'/.ai/guidelines/modules/feature.md'))->toBeTrue();
    expect(File::get($this->root.'/bootstrap/providers.php'))->toContain('declare(strict_types=1)');
    $this->manager->remove('feature');
    expect(File::get($this->root.'/.env'))->toBe("APP_NAME=API\n");
    expect(File::exists($this->root.'/.ai/guidelines/modules/feature.md'))->toBeFalse();
    expect($this->manager->doctor())->toBe([]);
});

it('rejects a manifest attempting to escape the project', function (): void {
    ($this->fixture)('feature');
    $path = $this->root.'/stubs/modules/feature/module.json';
    $manifest = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
    $manifest['files'] = ['feature.php' => '../escaped.php'];
    File::put($path, json_encode($manifest, JSON_THROW_ON_ERROR));
    expect(fn () => $this->manager->add(['feature']))->toThrow(RuntimeException::class, 'Unsafe module path');
    expect($this->manager->state()['modules'])->toBe([]);
});
