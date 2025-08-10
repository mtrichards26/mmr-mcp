### MCP PHP Server — Architecture Overview

This outlines a simple MCP (Model Context Protocol) server in PHP using an MCP framework with stdio transport. It exposes a single tool `time.now` that returns the current time for a requested IANA time zone. The design favors separation of concerns, testability, and protocol-compliant error handling.

## Runtime & Transport
- **PHP**: 8.2+ (Composer, PSR-4 autoload)
- **Transport**: stdio (long-lived process; JSON frames over stdin/stdout)
- **Protocol**: MCP with JSON-RPC-style request/response

## Composition Root (Bootstrap)
- Advertise server metadata and capabilities (tools)
- Configure stdio transport
- Register tools via a tool registry
- Optional PSR-3 logger to stderr
- Run main loop; exit gracefully on stdin close

## Tool: `time.now`
- **Purpose**: Return current time for a given IANA time zone
- **Input schema**: `{ timeZone: string }` (required), e.g., "America/New_York"
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
- No external config initially
- Optional future default timezone fallback to `UTC`

## Testing
- **Unit (PHPUnit)**:
  - Valid timezone → ISO-8601 time string
  - Invalid timezone → expected MCP error type/message
  - Use `FrozenClock`
- **Optional integration**:
  - Spawn entrypoint subprocess
  - Send `initialize` then `tools/call time.now`
  - Assert response structure

## Project Layout
```
/Users/mattrichards/source/mmr-mcp
├─ bin/
│  └─ mcp-time                 # entrypoint (#!/usr/bin/env php)
├─ src/
│  ├─ Bootstrap/ServerFactory.php
│  ├─ Domain/Clock/ClockInterface.php
│  ├─ Infrastructure/Clock/SystemClock.php
│  └─ Tools/TimeNowTool.php
├─ tests/
│  ├─ Unit/Tools/TimeNowToolTest.php
│  └─ Support/FrozenClock.php
├─ docs/
│  └─ arch-overview.md
├─ composer.json               # PSR-4 autoload, phpunit dev dep, scripts
└─ phpunit.xml.dist
```

## Dependencies (to confirm during implementation)
- PHP MCP server framework (server runtime, tool registration, stdio transport)
- `phpunit/phpunit` (dev)
- Optional: `monolog/monolog`, `phpstan/phpstan`

## MCP Flow (Happy Path)
1. Client launches server over stdio
2. Client sends `initialize`; server returns metadata and tool descriptors
3. Client calls `tools/call` for `time.now` with `{ timeZone }`
4. Server validates, invokes tool, returns `{ time }` or structured error
5. Server exits when stdin closes

## Non-Goals (Initial)
- No persistence, networking, auth, or external config

## Extensibility
- Register additional tools in the same composition root
- Reuse infrastructure (logging, validation, clocks)
- Swap transports if supported by the framework
