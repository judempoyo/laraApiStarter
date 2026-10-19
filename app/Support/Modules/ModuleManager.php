<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Throwable;

class ModuleManager
{
    private string $root;

    public function __construct(?string $root = null)
    {
        $this->root = $root ?? base_path();
    }

    public function catalog(): array
    {
        $modules = [];
        foreach (glob($this->root.'/stubs/modules/*/module.json') ?: [] as $path) {
            $manifest = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['name'] ?? null) !== basename(dirname($path))) {
                throw new RuntimeException("Invalid manifest: {$path}");
            }
            $modules[$manifest['name']] = $manifest;
        }

        return $modules;
    }

    public function state(): array
    {
        $path = $this->root.'/.api-starter.json';

        return is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : ['version' => 1, 'modules' => []];
    }

    public function resolve(array $names): array
    {
        $catalog = $this->catalog();
        $resolved = [];
        $visiting = [];
        $visit = function (string $name) use (&$visit, &$resolved, &$visiting, $catalog): void {
            if (isset($resolved[$name])) {
                return;
            }
            if (! isset($catalog[$name])) {
                throw new RuntimeException("Unknown module: {$name}");
            }
            if (isset($visiting[$name])) {
                throw new RuntimeException("Circular module dependency: {$name}");
            }
            $visiting[$name] = true;
            foreach ($catalog[$name]['requires'] as $dependency) {
                $visit($dependency);
            }
            unset($visiting[$name]);
            $resolved[$name] = $catalog[$name];
        };
        foreach ($names as $name) {
            $visit($name);
        }

        return $resolved;
    }

    public function add(array $names): array
    {
        $resolved = $this->resolve($names);

        return $this->transaction(function (array &$state, callable $write) use ($resolved): array {
            $added = [];
            foreach ($resolved as $name => $manifest) {
                if (isset($state['modules'][$name])) {
                    continue;
                }
                $files = [];
                foreach ($manifest['files'] as $source => $destination) {
                    $sourcePath = $this->path('stubs/modules/'.$name.'/'.$source);
                    $target = $this->path($destination);
                    if (! is_file($sourcePath)) {
                        throw new RuntimeException("Missing source: {$source}");
                    }
                    if (file_exists($target)) {
                        throw new RuntimeException("Refusing to overwrite existing file: {$destination}");
                    }
                    $content = file_get_contents($sourcePath);
                    $write($destination, $content);
                    $files[$destination] = hash('sha256', $content);
                }
                foreach ($manifest['patches'] ?? [] as $patch) {
                    if ($patch['type'] !== 'user-trait') {
                        throw new RuntimeException('Unsupported patch type.');
                    }
                    $this->traitPatch($patch['trait'], true, $write);
                }
                $provider = $manifest['provider'];
                $bootstrap = 'bootstrap/providers.php';
                // Journal the file before Laravel updates it.
                $write($bootstrap, file_get_contents($this->path($bootstrap)));
                if (! ServiceProvider::addProviderToBootstrapFile($provider, $this->path($bootstrap))) {
                    throw new RuntimeException("Cannot register provider: {$provider}");
                }
                $providers = file_get_contents($this->path($bootstrap));
                if (! str_contains($providers, 'declare(strict_types=1)')) {
                    $write($bootstrap, str_replace('<?php', "<?php\n\ndeclare(strict_types=1);", $providers));
                }
                if (($manifest['env'] ?? []) !== []) {
                    $env = is_file($this->path('.env')) ? file_get_contents($this->path('.env')) : '';
                    $block = "\n# api-starter:{$name}:start\n";
                    foreach ($manifest['env'] as $key => $entry) {
                        if (! preg_match('/^'.preg_quote($key, '/').'=/m', $env)) {
                            $block .= '# '.$entry['comment']."\n".$key.'='.$entry['default']."\n";
                        }
                    }
                    $write('.env', $env.$block."# api-starter:{$name}:end\n");
                }
                $state['modules'][$name] = ['version' => $manifest['version'], 'files' => $files, 'provider' => $provider, 'patches' => $manifest['patches'] ?? []];
                $added[] = $name;
            }

            return $added;
        });
    }

    public function remove(string $name): array
    {
        return $this->transaction(function (array &$state, callable $write) use ($name): array {
            if (! isset($state['modules'][$name])) {
                return [];
            }
            foreach ($state['modules'] as $installed => $entry) {
                if (in_array($name, $this->catalog()[$installed]['requires'], true)) {
                    throw new RuntimeException("Remove {$installed} first: it depends on {$name}.");
                }
            }
            $entry = $state['modules'][$name];
            foreach ($entry['files'] as $file => $hash) {
                $path = $this->path($file);
                if (is_file($path) && hash_file('sha256', $path) !== $hash) {
                    throw new RuntimeException("Locally modified module file: {$file}. Back up and restore it before removal.");
                }
            }
            foreach ($entry['patches'] as $patch) {
                $this->traitPatch($patch['trait'], false, $write);
            }
            $bootstrap = 'bootstrap/providers.php';
            $content = file_get_contents($this->path($bootstrap));
            $provider = preg_quote($entry['provider'], '/');
            $write($bootstrap, preg_replace('/^\s*\\\\?'.$provider.'::class,\s*$/m', '', $content));
            foreach ($entry['files'] as $file => $hash) {
                $write($file, null);
            }
            if (is_file($this->path('.env'))) {
                $env = file_get_contents($this->path('.env'));
                $write('.env', preg_replace('/\n# api-starter:'.preg_quote($name, '/').':start\n.*?# api-starter:'.preg_quote($name, '/').':end\n/s', '', $env));
            }
            unset($state['modules'][$name]);

            return [$name];
        });
    }

    public function doctor(): array
    {
        $issues = [];
        $state = $this->state();
        $catalog = $this->catalog();
        foreach ($state['modules'] as $name => $entry) {
            if (! isset($catalog[$name])) {
                $issues[] = "Unknown installed module: {$name}";

                continue;
            }
            foreach ($catalog[$name]['requires'] as $dependency) {
                if (! isset($state['modules'][$dependency])) {
                    $issues[] = "{$name}: missing dependency {$dependency}";
                }
            }
            foreach ($entry['files'] as $file => $hash) {
                if (! is_file($this->path($file))) {
                    $issues[] = "Missing file: {$file}";
                } elseif (hash_file('sha256', $this->path($file)) !== $hash) {
                    $issues[] = "Modified file: {$file}";
                }
            }
            $providers = file_get_contents($this->path('bootstrap/providers.php'));
            if (! str_contains($providers, $entry['provider'].'::class')) {
                $issues[] = "Missing provider: {$entry['provider']}";
            }
            foreach ($entry['patches'] as $patch) {
                if (! str_contains(file_get_contents($this->path('app/Models/User.php')), 'use \\'.$patch['trait'].';')) {
                    $issues[] = "Missing User trait: {$patch['trait']}";
                }
            }
            $env = is_file($this->path('.env')) ? file_get_contents($this->path('.env')) : '';
            foreach (array_keys($catalog[$name]['env'] ?? []) as $key) {
                if (! preg_match('/^'.preg_quote($key, '/').'=/m', $env)) {
                    $issues[] = "Missing environment key: {$key}";
                }
            }
        }
        foreach (glob($this->root.'/.ai/guidelines/modules/*.md') ?: [] as $file) {
            if (! isset($state['modules'][basename($file, '.md')])) {
                $issues[] = 'Orphan AI guideline: '.basename($file);
            }
        }

        foreach ($catalog as $name => $manifest) {
            if (isset($state['modules'][$name])) {
                continue;
            }
            foreach ($manifest['files'] as $destination) {
                if (str_starts_with($destination, '.ai/') && is_file($this->path($destination))) {
                    $issues[] = 'Orphan AI resource: '.$destination;
                }
            }
        }

        return $issues;
    }

    private function traitPatch(string $trait, bool $add, callable $write): void
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_\\\\]+$/', $trait)) {
            throw new RuntimeException('Invalid trait name.');
        }
        $file = 'app/Models/User.php';
        $content = file_get_contents($this->path($file));
        $anchor = '    // api-starter:traits';
        if (substr_count($content, $anchor) !== 1) {
            throw new RuntimeException('User trait anchor is missing or ambiguous.');
        }
        $line = '    use \\'.$trait.";\n";
        $content = str_replace($line, '', $content);
        $write($file, str_replace($anchor, ($add ? $line : '').$anchor, $content));
    }

    private function path(string $relative): string
    {
        if ($relative === '' || str_starts_with($relative, '/') || str_contains($relative, '\\') || in_array('..', explode('/', $relative), true)) {
            throw new RuntimeException("Unsafe module path: {$relative}");
        }
        $path = $this->root;
        foreach (explode('/', $relative) as $part) {
            $path .= '/'.$part;
            if (is_link($path)) {
                throw new RuntimeException("Symlink in module path: {$relative}");
            }
        }

        return $path;
    }

    private function transaction(callable $operation): array
    {
        $lock = fopen($this->root.'/.api-starter.lock', 'c');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('Another module operation is running.');
        }
        $originals = [];
        $write = function (string $relative, ?string $content) use (&$originals): void {
            $path = $this->path($relative);
            if (! array_key_exists($path, $originals)) {
                $originals[$path] = is_file($path) ? file_get_contents($path) : null;
            }
            if ($content === null) {
                if (is_file($path) && ! unlink($path)) {
                    throw new RuntimeException("Cannot remove {$relative}");
                }
            } else {
                if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0755, true) && ! is_dir(dirname($path))) {
                    throw new RuntimeException("Cannot create directory for {$relative}");
                }
                if (file_put_contents($path, $content) === false) {
                    throw new RuntimeException("Cannot write {$relative}");
                }
            }
        };
        try {
            $state = $this->state();
            $result = $operation($state, $write);
            $write('.api-starter.json', json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");

            return $result;
        } catch (Throwable $exception) {
            foreach (array_reverse($originals, true) as $path => $content) {
                if ($content === null) {
                    if (is_file($path)) {
                        unlink($path);
                    }
                } else {
                    file_put_contents($path, $content);
                }
            }
            throw $exception;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
