<?php

declare(strict_types=1);

namespace App;

use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

final class TimeElements implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger !== null) {
            $this->setLogger($logger);
        }
    }

    #[McpTool(name: 'time-now', description: 'Return current time in the specified IANA time zone (ISO-8601).')]
    public function now(
        #[Schema(description: 'IANA time zone, e.g., America/New_York')]
        string $timeZone
    ): string {
        $this->logger?->info('time-now called', ['timeZone' => $timeZone]);
        try {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($timeZone));
        } catch (\Throwable $e) {
            $this->logger?->warning('Invalid time zone', ['timeZone' => $timeZone, 'error' => $e->getMessage()]);
            throw new \InvalidArgumentException("Invalid time zone: {$timeZone}");
        }
        $iso = $dt->format(\DateTimeInterface::ATOM);
        $this->logger?->debug('time-now computed time', ['time' => $iso]);
        return $iso;
    }
}
