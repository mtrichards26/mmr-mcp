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

    // Choose output stream: avoid STDOUT in stdio mode to prevent corrupting JSON-RPC frames
    $outputStream = ($config->transport === TransportConfig::TRANSPORT_STDIO) ? STDERR : STDOUT;

    // Print effective configuration
    fwrite($outputStream, "MCP Server configuration:\n");
    fwrite($outputStream, sprintf("  transport: %s\n", $config->transport));
    if ($config->transport === TransportConfig::TRANSPORT_HTTP) {
        fwrite($outputStream, sprintf("  host: %s\n", $config->host));
        fwrite($outputStream, sprintf("  port: %d\n", $config->port));
        fwrite($outputStream, sprintf("  path: %s\n", $config->path));
        fwrite($outputStream, sprintf("  json_response: %s\n", $config->enableJsonResponse ? 'true' : 'false'));
        fwrite($outputStream, sprintf("  stateless: %s\n", $config->stateless ? 'true' : 'false'));
        fwrite($outputStream, "\n");
    }

    // Announce when ready
    $transport->once('ready', function () use ($outputStream, $config): void {
        if ($config->transport === TransportConfig::TRANSPORT_HTTP) {
            $scheme = 'http';
            $base = sprintf('%s://%s:%d', $scheme, $config->host, $config->port);
            fwrite($outputStream, sprintf("Server started and listening at %s%s\n", $base, $config->path));
        } else {
            fwrite($outputStream, "Server started and listening on stdio\n");
        }
    });

    $server->listen($transport);
} catch (\Throwable $e) {
    fwrite(STDERR, "[CRITICAL] {$e->getMessage()}\n");
    exit(1);
}
