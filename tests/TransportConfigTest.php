<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Config\TransportConfig;

final class TransportConfigTest extends TestCase
{
    public function test_defaults_stdio(): void
    {
        $cfg = TransportConfig::fromEnvAndArgs([], []);
        $this->assertSame('stdio', $cfg->transport);
    }

    public function test_env_http_overrides(): void
    {
        $cfg = TransportConfig::fromEnvAndArgs([
            'MCP_TRANSPORT' => 'http',
            'MCP_HTTP_HOST' => '127.0.0.1',
            'MCP_HTTP_PORT' => '9090',
            'MCP_HTTP_PATH' => '/mcp',
            'MCP_HTTP_JSON' => 'false',
            'MCP_HTTP_STATELESS' => 'true',
        ], []);

        $this->assertSame('http', $cfg->transport);
        $this->assertSame('127.0.0.1', $cfg->host);
        $this->assertSame(9090, $cfg->port);
        $this->assertSame('/mcp', $cfg->path);
        $this->assertFalse($cfg->enableJsonResponse);
        $this->assertTrue($cfg->stateless);
    }

    public function test_args_override_env(): void
    {
        $env = [
            'MCP_TRANSPORT' => 'stdio',
            'MCP_HTTP_PORT' => '8080',
        ];
        $cfg = TransportConfig::fromEnvAndArgs($env, ['script.php', '--transport=http', '--port=6060', '--path=mcp', '--json=false']);
        $this->assertSame('http', $cfg->transport);
        $this->assertSame(6060, $cfg->port);
        $this->assertSame('/mcp', $cfg->path);
        $this->assertFalse($cfg->enableJsonResponse);
    }
}
