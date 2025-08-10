<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\TimeElements;

final class TimeElementsTest extends TestCase
{
    public function test_now_valid_timezone_returns_iso8601(): void
    {
        $tool = new TimeElements();
        $iso = $tool->now('UTC');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:[+\-]\d{2}:\d{2}|Z)$/', $iso);
    }

    public function test_now_invalid_timezone_throws(): void
    {
        $tool = new TimeElements();
        $this->expectException(\InvalidArgumentException::class);
        $tool->now('Not/AZone');
    }
}
