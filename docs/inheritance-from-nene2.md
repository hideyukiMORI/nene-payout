# NENE2 Inheritance Map — NeNe Payout

NeNe Payout inherits NENE2 conventions. See [ADR 0001](./adr/0001-inherit-nene2-governance.md).

## What is inherited

| Concern | NENE2 component | Notes |
| --- | --- | --- |
| HTTP runtime | PSR-15 middleware stack | Front controller at `public_html/index.php` |
| Routing | `Router` | Explicit route table |
| Error responses | `ProblemDetailsResponseFactory` | RFC 9457 `application/problem+json` |
| Authentication | `BearerAuth` + `NENE2_LOCAL_JWT_SECRET` | JWT HS256 |
| JSON response | `JsonResponseFactory` | |
| Validation | `ValidationException` + `ValidationError` | 422 auto-mapping |
| Pagination | `PaginationQuery` | |
| DB connection | `DatabaseQueryExecutorInterface` | |
| Upsert | `DbUpsert::run()` | Cross-driver (SQLite + MySQL) |
| Schema migrations | Phinx | `database/migrations/` |
| DI | PSR-11 | Explicit wiring in `ServiceProvider` |
| Code style | PHP-CS-Fixer | `composer cs:fix` |
| Static analysis | PHPStan level 8 | `composer analyse` |

## What is NOT inherited

- MCP tools — NeNe Payout does not expose MCP tools in Phase 1
- Smarty templates — admin UI uses React
- NeNe Kit helpers — use only when a specific Kit class is needed
