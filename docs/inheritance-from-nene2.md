# NENE2 Inheritance Map — NeNe Payout

NeNe Payout inherits NENE2 conventions. See [ADR 0001](./adr/0001-inherit-nene2-governance.md).

## What is inherited

Exact class names and usage: [`docs/development/nene2-runtime-reference.md`](./development/nene2-runtime-reference.md).

| Concern | NENE2 component (exact) | Notes |
| --- | --- | --- |
| HTTP runtime | PSR-7/15/17 + `MiddlewareDispatcher` | Front controller at `public_html/index.php` |
| Routing | `Router` | Explicit route table; params under `Router::PARAMETERS_ATTRIBUTE` |
| Error responses | `ProblemDetailsResponseFactory` + `ErrorHandlerMiddleware` | RFC 9457 `application/problem+json` |
| Authentication | `BearerTokenMiddleware` + `TokenVerifierInterface`; local `LocalBearerTokenVerifier` + `NENE2_LOCAL_JWT_SECRET` | JWT HS256 |
| JSON response | `JsonResponseFactory` | |
| Validation | `ValidationException` + `ValidationError` + `V` | 422 mapping |
| Pagination | `PaginationQuery` / `PaginationQueryParser` / `PaginationResponse` | |
| DB read/write | `DatabaseQueryExecutorInterface` | injected; no raw PDO |
| DB connection | `DatabaseConnectionFactoryInterface` / `PdoConnectionFactory` | from typed `DatabaseConfig` |
| DB transactions | `DatabaseTransactionManagerInterface` | multi-query atomic work |
| Clock | `ClockInterface` → `UtcClock` | injectable, UTC (ADR 0012) |
| Security headers / CORS / size / request id | `SecurityHeadersMiddleware` / `CorsMiddleware` / `RequestSizeLimitMiddleware` / `RequestIdMiddleware` | baseline middleware |
| Schema migrations | Phinx | `database/migrations/` |
| DI | PSR-11 `Container` / `ContainerBuilder` / `ServiceProviderInterface` | explicit wiring |
| Config | `AppConfig` / `DatabaseConfig` via `ConfigLoader` | typed; no `getenv()` in app code |
| Code style | PHP-CS-Fixer | `composer cs:fix` |
| Static analysis | PHPStan level 8 | `composer analyse` |

> Note: NENE2 has **no** `BearerAuth`, `DbUpsert`, `PdoConnection::getInstance()`,
> or `ResponseDecorator`. Cross-driver upsert is done with explicit SQL / the
> query executor, not a helper.

## What is NOT inherited

- MCP tools — NeNe Payout does not expose MCP tools in Phase 1
- Smarty templates — admin UI uses React
- NeNe Kit helpers — use only when a specific Kit class is needed
