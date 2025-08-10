## MCP Time Server (PHP)

A minimal MCP (Model Context Protocol) server implemented in PHP that communicates over stdio and exposes a single tool to return the current time for a requested IANA time zone.

### Overview
- Runtime: PHP 8.2+ (Composer, PSR-4 autoload)
- Transport: stdio (stdin/stdout)
- Protocol: MCP (JSON-RPC-style requests/responses)

### Exposed Tools
- **time.now**
  - **Input**: `{ timeZone: string }` (required). Example: "America/New_York"
  - **Output**: `{ time: string }` — ISO-8601 timestamp (`DateTimeImmutable::ATOM`).
  - **Behavior**: Validates the time zone and returns the current time for that zone; returns a structured error for invalid zones.

### Usage (VS Code)
- Install dependencies: `composer install`
- Configure your VS Code MCP client with an `mcp.json` that points to the stdio server entrypoint. Different clients expect different top-level keys. Many VS Code clients use a top-level `servers` object; others use `mcpServers`.

Example (top-level `servers`):
```json
{
  "servers": {
    "php-time": {
      "command": "php",
      "args": [
        "<path_to_mmr-mcp>/bin/mcp-server.php"
      ],
      "env": {}
    }
  }
}
```

- Adjust the path placeholder to the absolute path of `bin/mcp-server.php` on your machine.
- Ensure no output is written to STDOUT from your tools; use STDERR for debug logs.

### More Details
See the architecture overview for deeper context, design goals, and project layout:
- [Architecture Overview](./docs/arch-overview.md)
