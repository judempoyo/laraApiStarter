<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
$state = json_decode(file_get_contents($root.'/.api-starter.json'), true, 512, JSON_THROW_ON_ERROR);
foreach (array_reverse(array_keys($state['modules'])) as $module) {
    $process = new Process([PHP_BINARY, 'artisan', 'api:remove', $module, '--no-interaction'], $root);
    $process->setTimeout(180);
    $process->mustRun(static fn ($type, $buffer) => print $buffer);
}
