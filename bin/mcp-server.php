#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpMcp\Server\Server;
use App\Config\TransportConfig;
use App\Transport\TransportFactory;
use App\Bootstrap\LoggerFactory;
use PhpMcp\Server\Defaults\BasicContainer;
use Psr\Log\LoggerInterface;

try {
    $logger = LoggerFactory::make('mcp');

    // Provide a basic container and register the logger instance so handlers can be constructed with it
    $container = new BasicContainer();
    $container->set(LoggerInterface::class, $logger);

    $server = Server::make()
        ->withServerInfo('PHP Time Server', '0.2.0')
        ->withLogger($logger)
        ->withContainer($container)
        ->build();

    $logger->info('Server built');

    // Discover MCP elements in src/
    $server->discover(basePath: dirname(__DIR__), scanDirs: ['src']);
    $logger->info('Discovery completed', ['basePath' => dirname(__DIR__), 'scanDirs' => ['src']]);

    // Select transport via env/args
    $config = TransportConfig::fromEnvAndArgs($_ENV + getenv(), $_SERVER['argv'] ?? []);
    $transport = TransportFactory::make($config);

    if ($config->transport === TransportConfig::TRANSPORT_HTTP) {
        // Print effective configuration and a ready message to STDOUT
        fwrite(STDOUT, "MCP Server configuration:\n");
        fwrite(STDOUT, sprintf("  transport: %s\n", $config->transport));
        fwrite(STDOUT, sprintf("  host: %s\n", $config->host));
        fwrite(STDOUT, sprintf("  port: %d\n", $config->port));
        fwrite(STDOUT, sprintf("  path: %s\n", $config->path));
        fwrite(STDOUT, sprintf("  json_response: %s\n", $config->enableJsonResponse ? 'true' : 'false'));
        fwrite(STDOUT, sprintf("  stateless: %s\n\n", $config->stateless ? 'true' : 'false'));
    }

    $logger->info('Transport configured', [
        'transport' => $config->transport,
        'host' => $config->host,
        'port' => $config->port,
        'path' => $config->path,
        'json_response' => $config->enableJsonResponse,
        'stateless' => $config->stateless,
    ]);

    $transport->once('ready', function () use ($config, $logger): void {
        if ($config->transport === TransportConfig::TRANSPORT_HTTP) {
            $scheme = 'http';
            $base = sprintf('%s://%s:%d', $scheme, $config->host, $config->port);
            fwrite(STDOUT, sprintf("Server started and listening at %s%s\n", $base, $config->path));
        }
        $logger->info('Server ready');
    });

    $logger->info('Starting listener');
    $server->listen($transport);
} catch (\Throwable $e) {
    // Critical errors to STDERR and logger
    fwrite(STDERR, "[CRITICAL] {$e->getMessage()}\n");
    if (isset($logger)) {
        $logger->error('Fatal error', ['exception' => $e]);
    }
    exit(1);
}
