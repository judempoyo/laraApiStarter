<?php

declare(strict_types=1);

$processes = [];
foreach ([['serve'], ['queue:listen', '--tries=1'], ['pail', '--timeout=0']] as $arguments) {
    $process = proc_open([PHP_BINARY, 'artisan', ...$arguments], [STDIN, STDOUT, STDERR], $pipes, dirname(__DIR__));
    if (! is_resource($process)) {
        throw new RuntimeException('Unable to start development process.');
    }
    $processes[] = $process;
}
register_shutdown_function(static function () use (&$processes): void {
    foreach ($processes as $process) {
        proc_terminate($process);
        proc_close($process);
    }
});
while (true) {
    foreach ($processes as $process) {
        if (! proc_get_status($process)['running']) {
            exit(1);
        }
    }
    usleep(200000);
}
