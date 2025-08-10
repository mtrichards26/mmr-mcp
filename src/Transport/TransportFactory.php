<?php

declare(strict_types=1);

namespace App\Transport;

use App\Config\TransportConfig;
use PhpMcp\Server\Transports\StdioServerTransport;
use PhpMcp\Server\Transports\StreamableHttpServerTransport;
use PhpMcp\Server\Contracts\ServerTransportInterface;

final class TransportFactory
{
    public static function make(TransportConfig $config): ServerTransportInterface
    {
        if ($config->transport === TransportConfig::TRANSPORT_STDIO) {
            return new StdioServerTransport();
        }

        return new StreamableHttpServerTransport(
            host: $config->host,
            port: $config->port,
            mcpPath: $config->path,
            sslContext: null,
            enableJsonResponse: $config->enableJsonResponse,
            stateless: $config->stateless,
        );
    }
}
