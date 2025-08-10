<?php

declare(strict_types=1);

namespace App\Bootstrap;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class LoggerFactory
{
    public static function make(string $channel = 'mcp'): LoggerInterface
    {
        $logger = new Logger($channel);

        $logDir = __DIR__ . '/../../logs';

        // File handler at INFO+; fallback silently if path cannot be opened
        try {
            $fileHandler = new StreamHandler($logDir . '/mcp-out.log', Level::Info);
            $logger->pushHandler($fileHandler);
        } catch (\Throwable) {
            // If file cannot be opened, we still have STDERR below
        }

        // STDERR handler at ERROR+
        $stderrHandler = new StreamHandler('php://stderr', Level::Error);
        $logger->pushHandler($stderrHandler);

        return $logger;
    }
}
