<?php

declare(strict_types=1);

namespace App;

use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

final class TimeElements
{
    #[McpTool(name: 'time.now', description: 'Return current time in the specified IANA time zone (ISO-8601).')]
    public function now(
        #[Schema(description: 'IANA time zone, e.g., America/New_York')]
        string $timeZone
    ): string {
        try {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone($timeZone));
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Invalid time zone: {$timeZone}");
        }
        return $dt->format(\DateTimeInterface::ATOM);
    }
}
