## MCP Time Server (PHP)

A minimal MCP (Model Context Protocol) server implemented in PHP that communicates over stdio and exposes a single tool to return the current time for a requested IANA time zone.

### Overview
- Runtime: PHP 8.2+ (Composer, PSR-4 autoload)
- Transport: stdio (stdin/stdout)
- Protocol: MCP (JSON-RPC-style requests/responses)

### Exposed Tools
- **time.now**
  - **Input**: `{ timeZone: string }` (required). Example: `"America/New_York"`
  - **Output**: `{ time: string }` — ISO-8601 timestamp (`DateTimeImmutable::ATOM`).
  - **Behavior**: Validates the time zone and returns the current time for that zone; returns a structured error for invalid zones.

### More Details
See the architecture overview for deeper context, design goals, and project layout:
- [Architecture Overview](./docs/arch-overview.md)
