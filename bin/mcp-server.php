#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;

try {
    $server = Server::make()
        ->withServerInfo('PHP Time Server', '0.1.0')
        ->build();

    // Discover MCP elements in src/
    $server->discover(basePath: dirname(__DIR__), scanDirs: ['src']);

    $transport = new StdioServerTransport();
    $server->listen($transport);
} catch (\Throwable $e) {
    fwrite(STDERR, "[CRITICAL] {$e->getMessage()}\n");
    exit(1);
}
