### MCP PHP Server — Architecture Overview

This outlines a simple MCP (Model Context Protocol) server in PHP using an MCP framework with stdio transport. It exposes a single tool `time.now` that returns the current time for a requested IANA time zone. The design favors separation of concerns, testability, and protocol-compliant error handling.

## Runtime & Transport
- **PHP**: 8.2+ (Composer, PSR-4 autoload)
- **Transports**:
  - `stdio` (default): long-lived process; JSON frames over stdin/stdout
  - `Streamable HTTP`: event-driven HTTP server with resumability/JSON-response mode
- **Protocol**: MCP with JSON-RPC-style request/response

## Composition Root (Bootstrap)
- Advertise server metadata and capabilities (tools)
- Discover tools via attribute scanning in `src/`
- Select transport using `App\Config\TransportConfig` and `App\Transport\TransportFactory`
- Run main loop; exit gracefully on transport close

## Transport Selection (Config)
- `TransportConfig` parses env + CLI args:
  - `MCP_TRANSPORT`: `stdio` (default) or `http`
  - `MCP_HTTP_HOST` (default `127.0.0.1`)
  - `MCP_HTTP_PORT` (default `8080`)
  - `MCP_HTTP_PATH` (default `/mcp`)
  - `MCP_HTTP_JSON` (`true` returns direct JSON on POST)
  - `MCP_HTTP_STATELESS` (`false` by default)
- CLI flags (override env): `--transport=`, `--host=`, `--port=`, `--path=`, `--json=`, `--stateless=`
- `TransportFactory` builds either `StdioServerTransport` or `StreamableHttpServerTransport` using the parsed config

## Tool: `time.now`
- **Purpose**: Return current time for a given IANA time zone
- **Input schema**: `{ timeZone: string }` (required), example "America/New_York"
- **Output schema**: `{ time: string }` where `time` is ISO-8601 (`DateTimeImmutable::ATOM`)
- **Behavior**:
  - Validate `timeZone`; on invalid value, return structured MCP error
  - Use injected clock to obtain "now" in the given zone
  - No external I/O

## Domain/Service Layer
- `ClockInterface` with `now(DateTimeZone): DateTimeImmutable`
- `SystemClock` uses PHP `DateTimeImmutable`
- `FrozenClock` for deterministic tests

## Error Handling
- Map validation/domain failures to MCP error responses with clear codes/messages
- Never crash on bad input; always emit well-formed error
- Log unexpected exceptions to stderr with request correlation (if available)

## Observability
- Minimal structured logging to stderr for lifecycle and errors

## Configuration
- No external config initially for tools
- Transport controlled via env/CLI (see above)

## Testing
- **Unit (PHPUnit)**:
  - Valid timezone → ISO-8601 time string
  - Invalid timezone → expected MCP error type/message
  - `TransportConfigTest` validates env/arg parsing & precedence
- **Optional integration**:
  - Spawn entrypoint subprocess for stdio
  - For HTTP, send `initialize`/`tools/call` to the configured endpoint

## Project Layout
```
/Users/mattrichards/source/mmr-mcp
├─ bin/
│  └─ mcp-server.php            # entrypoint (transport selection via env/args)
├─ src/
│  ├─ Bootstrap/ServerFactory.php
│  ├─ Config/TransportConfig.php
│  ├─ Transport/TransportFactory.php
│  ├─ Domain/Clock/ClockInterface.php
│  ├─ Infrastructure/Clock/SystemClock.php
│  └─ Tools/TimeNowTool.php
├─ tests/
│  ├─ Unit/Tools/TimeNowToolTest.php
│  ├─ TransportConfigTest.php
│  └─ Support/FrozenClock.php
├─ docs/
│  └─ arch-overview.md
├─ composer.json               # PSR-4 autoload, phpunit dev dep, scripts
└─ phpunit.xml.dist
```

## Dependencies (to confirm during implementation)
- PHP MCP server framework (server runtime, tool registration, stdio & streamable HTTP transports)
- `phpunit/phpunit` (dev)
- Optional: `monolog/monolog`, `phpstan/phpstan`

## MCP Flow (Happy Path)
1. Client launches server over stdio OR connects to HTTP endpoint
2. Client sends `initialize`; server returns metadata and tool descriptors
3. Client calls `tools/call` for `time.now`
4. Server validates, invokes tool, returns `{ time }` or structured error
5. Server exits when stdin closes (stdio) or transport stops (HTTP)

## Non-Goals (Initial)
- No persistence, networking, auth, or external config for tools

## Extensibility
- Register additional tools in the same composition root
- Reuse infrastructure (logging, validation, clocks)
- Swap transports or tune HTTP options via the config without code changes
