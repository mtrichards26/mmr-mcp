<?php

declare(strict_types=1);

namespace App\Config;

final class TransportConfig
{
    public const TRANSPORT_STDIO = 'stdio';
    public const TRANSPORT_HTTP = 'http';

    public function __construct(
        public readonly string $transport, // 'stdio' | 'http'
        public readonly string $host = '127.0.0.1',
        public readonly int $port = 8080,
        public readonly string $path = '/mcp',
        public readonly bool $enableJsonResponse = true,
        public readonly bool $stateless = false
    ) {
        if (!in_array($this->transport, [self::TRANSPORT_STDIO, self::TRANSPORT_HTTP], true)) {
            throw new \InvalidArgumentException('Invalid transport: ' . $this->transport);
        }
        if ($this->transport === self::TRANSPORT_HTTP) {
            if ($this->host === '') {
                throw new \InvalidArgumentException('HTTP host cannot be empty');
            }
            if ($this->port < 1 || $this->port > 65535) {
                throw new \InvalidArgumentException('HTTP port must be between 1 and 65535');
            }
            if ($this->path === '') {
                throw new \InvalidArgumentException('HTTP path cannot be empty');
            }
        }
    }

    public static function fromEnvAndArgs(array $env, array $argv): self
    {
        // Defaults
        $transport = self::TRANSPORT_STDIO;
        $host = '127.0.0.1';
        $port = 8080;
        $path = '/mcp';
        $enableJson = true;
        $stateless = false;

        // Env
        $transport = self::valueOr($env['MCP_TRANSPORT'] ?? null, $transport);
        $host = self::valueOr($env['MCP_HTTP_HOST'] ?? null, $host);
        $port = (int) self::valueOr($env['MCP_HTTP_PORT'] ?? null, (string) $port);
        $path = self::valueOr($env['MCP_HTTP_PATH'] ?? null, $path);
        $enableJson = self::boolOr($env['MCP_HTTP_JSON'] ?? null, $enableJson);
        $stateless = self::boolOr($env['MCP_HTTP_STATELESS'] ?? null, $stateless);

        // Args like --key=value
        foreach (self::parseArgs($argv) as $key => $value) {
            switch ($key) {
                case 'transport':
                    $transport = $value;
                    break;
                case 'host':
                    $host = $value;
                    break;
                case 'port':
                    $port = (int) $value;
                    break;
                case 'path':
                    $path = $value;
                    break;
                case 'json':
                    $enableJson = self::boolOr($value, $enableJson);
                    break;
                case 'stateless':
                    $stateless = self::boolOr($value, $stateless);
                    break;
            }
        }

        // Normalize path to start with '/'
        if ($path !== '' && $path[0] !== '/') {
            $path = '/' . $path;
        }

        return new self(
            transport: strtolower($transport),
            host: $host,
            port: $port,
            path: $path,
            enableJsonResponse: $enableJson,
            stateless: $stateless
        );
    }

    private static function parseArgs(array $argv): array
    {
        $result = [];
        foreach ($argv as $arg) {
            if (!is_string($arg)) {
                continue;
            }
            if (!str_starts_with($arg, '--')) {
                continue;
            }
            $arg = substr($arg, 2);
            $parts = explode('=', $arg, 2);
            $key = $parts[0] ?? '';
            $val = $parts[1] ?? 'true';
            if ($key !== '') {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    private static function valueOr(?string $value, string $default): string
    {
        return ($value === null || $value === '') ? $default : $value;
    }

    private static function boolOr(null|string $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = strtolower((string) $value);
        return in_array($v, ['1', 'true', 'yes', 'on'], true) ? true : (in_array($v, ['0', 'false', 'no', 'off'], true) ? false : $default);
    }
}
