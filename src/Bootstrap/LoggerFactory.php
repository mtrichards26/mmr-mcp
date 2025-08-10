<?php

declare(strict_types=1);

namespace App\Bootstrap;

use Monolog\Handler\RotatingFileHandler;
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
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        // Rotating file handler: daily logs, keep last 5 files
        try {
            $rotating = new RotatingFileHandler($logDir . '/mcp-out.log', 5, Level::Info);
            $logger->pushHandler($rotating);
        } catch (\Throwable) {
            // If file cannot be opened, fallback only to STDERR
        }

        // STDERR handler at ERROR+
        $stderrHandler = new StreamHandler('php://stderr', Level::Error);
        $logger->pushHandler($stderrHandler);

        return $logger;
    }
}
