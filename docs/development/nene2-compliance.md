# NENE2 Compliance — NeNe Payout

This file is **binding**. Violations block merge.

Inherits [NENE2 coding standards](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/coding-standards.md). Deviations require a local ADR. The full common-object catalog with exact class names and namespaces is [`nene2-runtime-reference.md`](./nene2-runtime-reference.md).

## Required framework objects (do not reinvent)

Exact NENE2 classes (`Nene2\` namespace). See `nene2-runtime-reference.md` §1 for the full list.

| Concern | Use this (exact) | Never reinvent |
| --- | --- | --- |
| JSON response | `JsonResponseFactory` (`Nene2\Http`) | custom `json_encode()` responses |
| Problem Details error | `ProblemDetailsResponseFactory` (`Nene2\Error`) | custom error arrays |
| Pagination | `PaginationQuery` / `PaginationResponse` (`Nene2\Http`) | custom page/offset parsing |
| Bearer auth | `BearerTokenMiddleware` + `TokenVerifierInterface` (`Nene2\Auth`); local: `LocalBearerTokenVerifier` + `NENE2_LOCAL_JWT_SECRET` | custom JWT parsing |
| Validation | `ValidationException` + `ValidationError` + `V` (`Nene2\Validation`) | custom validation response |
| DB read/write | `DatabaseQueryExecutorInterface` (`Nene2\Database`) | raw `PDO`, custom wrapper |
| DB connection | `DatabaseConnectionFactoryInterface` / `PdoConnectionFactory` (`Nene2\Database`) | `new PDO()`, singletons / `getInstance()` |
| DB transaction | `DatabaseTransactionManagerInterface` (`Nene2\Database`) | manual `BEGIN`/`COMMIT` |
| Request ID | `RequestIdMiddleware` (`Nene2\Middleware`) | custom request ID header |
| Security headers | `SecurityHeadersMiddleware` (`Nene2\Middleware`) | ad-hoc header setting |
| CORS | `CorsMiddleware` (`Nene2\Middleware`) | hard-coded origins |
| Clock (now) | `ClockInterface` → `UtcClock` (`Nene2\Http`) | ambient `date()` |
| Container / wiring | `Container` / `ContainerBuilder` / `ServiceProviderInterface` (`Nene2\DependencyInjection`) | service locator in domain code |

> There is **no** `PdoConnection::getInstance()`, `DbUpsert`, `BearerAuth`, or
> `ResponseDecorator` in NENE2. Use the classes above.

## Architecture rules (binding)

- Handler → UseCase → RepositoryInterface → PdoRepository
- No business logic in Handler
- No HTTP knowledge in UseCase
- No raw SQL outside Repository
- DI via constructor injection; no `new` in UseCase

## Middleware stack order (binding)

Inherits NENE2 order (`ErrorHandlerMiddleware` wraps the whole pipeline):

```
1. ErrorHandlerMiddleware        (wraps everything)
2. RequestIdMiddleware
3. SecurityHeadersMiddleware
4. CorsMiddleware
5. RequestSizeLimitMiddleware
6. BearerTokenMiddleware / ApiKeyAuthenticationMiddleware
7. (request / OpenAPI validation, when added)
8. Router / dispatch
```

See [`nene2-runtime-reference.md`](./nene2-runtime-reference.md) §4.

## Money (binding)

Integer cents only. No floats. No `DECIMAL` in SQLite test schemas.

## Tenant isolation (binding)

Every query on a tenant table includes `organization_id` in WHERE. The org is resolved from the request by `OrgResolverMiddleware` and read from a `RequestScopedHolder` in the repository (ADR 0018); never derive it from the request body/path/query. See [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md).

## Verification commands

```bash
composer check     # PHPUnit + PHPStan level 8 + CS-Fixer + OpenAPI
composer test      # PHPUnit only
composer analyse   # PHPStan only
composer cs        # CS-Fixer dry-run
composer cs:fix    # CS-Fixer auto-fix
composer openapi   # OpenAPI contract validation
```
