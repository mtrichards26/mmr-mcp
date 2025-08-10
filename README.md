## MCP Time Server (PHP)

A minimal MCP (Model Context Protocol) server implemented in PHP that communicates over stdio and exposes a single tool to return the current time for a requested IANA time zone.

### Overview
- Runtime: PHP 8.2+ (Composer, PSR-4 autoload)
- Transport: stdio (stdin/stdout) or Streamable HTTP
- Protocol: MCP (JSON-RPC-style requests/responses)

### Exposed Tools
- **time.now**
  - **Input**: `{ timeZone: string }` (required). Example: "America/New_York"
  - **Output**: `{ time: string }` — ISO-8601 timestamp (`DateTimeImmutable::ATOM`).
  - **Behavior**: Validates the time zone and returns the current time for that zone; returns a structured error for invalid zones.

### Transport Selection
You can switch transports without code changes using environment variables or CLI flags.

- Defaults to `stdio`.
- Env vars:
  - `MCP_TRANSPORT`: `stdio` (default) or `http`
  - `MCP_HTTP_HOST`: default `127.0.0.1`
  - `MCP_HTTP_PORT`: default `8080`
  - `MCP_HTTP_PATH`: default `/mcp`
  - `MCP_HTTP_JSON`: `true|false` (direct JSON response on POST)
  - `MCP_HTTP_STATELESS`: `true|false`
- CLI flags (override env): `--transport=`, `--host=`, `--port=`, `--path=`, `--json=`, `--stateless=`

Examples:
- Stdio (default):
  ```bash
  php <path_to_mmr-mcp>/bin/mcp-server.php
  ```
- Streamable HTTP (JSON mode):
  ```bash
  MCP_TRANSPORT=http MCP_HTTP_HOST=127.0.0.1 MCP_HTTP_PORT=8080 MCP_HTTP_PATH=/mcp MCP_HTTP_JSON=true php <path_to_mmr-mcp>/bin/mcp-server.php
  # or with flags (flags override env):
  php <path_to_mmr-mcp>/bin/mcp-server.php --transport=http --host=127.0.0.1 --port=8080 --path=/mcp --json=true
  ```
- Streamable HTTP (SSE mode, not stateless):
  ```bash
  php <path_to_mmr-mcp>/bin/mcp-server.php --transport=http --json=false --stateless=false
  ```

### Usage (VS Code)
- Install dependencies: `composer install`
- Configure your VS Code MCP client with an `mcp.json` that points to the server. Many VS Code clients use a top-level `servers` object.

Stdio example:
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

HTTP example (requires starting the server in HTTP mode as shown above):
```json
{
  "servers": {
    "php-time-http": {
      "url": "http://127.0.0.1:8080/mcp"
    }
  }
}
```

- Adjust placeholders and ports to match your environment.
- Ensure no output is written to STDOUT from your tools; use STDERR for debug logs.

### More Details
See the architecture overview for deeper context, design goals, and project layout:
- [Architecture Overview](./docs/arch-overview.md)
