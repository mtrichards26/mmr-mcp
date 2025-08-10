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

    // In HTTP mode, print effective configuration and a ready message to STDOUT
    if ($config->transport === TransportConfig::TRANSPORT_HTTP) {
        fwrite(STDOUT, "MCP Server configuration:\n");
        fwrite(STDOUT, sprintf("  transport: %s\n", $config->transport));
        fwrite(STDOUT, sprintf("  host: %s\n", $config->host));
        fwrite(STDOUT, sprintf("  port: %d\n", $config->port));
        fwrite(STDOUT, sprintf("  path: %s\n", $config->path));
        fwrite(STDOUT, sprintf("  json_response: %s\n", $config->enableJsonResponse ? 'true' : 'false'));
        fwrite(STDOUT, sprintf("  stateless: %s\n\n", $config->stateless ? 'true' : 'false'));

        $transport->once('ready', function () use ($config): void {
            $scheme = 'http';
            $base = sprintf('%s://%s:%d', $scheme, $config->host, $config->port);
            fwrite(STDOUT, sprintf("Server started and listening at %s%s\n", $base, $config->path));
        });
    }

    $server->listen($transport);
} catch (\Throwable $e) {
    fwrite(STDERR, "[CRITICAL] {$e->getMessage()}\n");
    exit(1);
}
