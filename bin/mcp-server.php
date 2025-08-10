#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpMcp\Server\Server;
use App\Config\TransportConfig;
use App\Transport\TransportFactory;

try {
    $server = Server::make()
        ->withServerInfo('PHP Time Server', '0.2.0')
        ->build();

    // Discover MCP elements in src/
    $server->discover(basePath: dirname(__DIR__), scanDirs: ['src']);

    // Select transport via env/args
    $config = TransportConfig::fromEnvAndArgs($_ENV + getenv(), $_SERVER['argv'] ?? []);
    $transport = TransportFactory::make($config);

    $server->listen($transport);
} catch (\Throwable $e) {
    fwrite(STDERR, "[CRITICAL] {$e->getMessage()}\n");
    exit(1);
}
