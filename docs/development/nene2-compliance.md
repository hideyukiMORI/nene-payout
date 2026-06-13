# NENE2 Compliance — NeNe Payout

This file is **binding**. Violations block merge.

Inherits [NENE2 ADR-0014 / coding standards](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/coding-standards.md). Deviations require a local ADR.

## Required framework objects (do not reinvent)

| Concern | Use this | Never reinvent |
| --- | --- | --- |
| JSON response | `JsonResponseFactory` | custom `json_encode()` responses |
| Problem Details error | `ProblemDetailsResponseFactory` | custom error arrays |
| Pagination | `PaginationQuery` | custom page/offset parsing |
| Bearer auth | `BearerAuth` + `NENE2_LOCAL_JWT_SECRET` | custom JWT parsing |
| Validation error | `ValidationException` + `ValidationError` | custom validation response |
| Upsert | `DbUpsert::run()` | driver-specific INSERT … ON DUPLICATE KEY |
| DB connection | `PdoConnection::getInstance()` | custom PDO wrapper |
| Request ID | `RequestId` middleware | custom request ID header |
| Security headers | `ResponseDecorator` | ad-hoc header setting |

## Architecture rules (binding)

- Handler → UseCase → RepositoryInterface → PdoRepository
- No business logic in Handler
- No HTTP knowledge in UseCase
- No raw SQL outside Repository
- DI via constructor injection; no `new` in UseCase

## Middleware stack order (binding)

Inherits NENE2 order:

```
1. RequestId
2. RequestLogging
3. SecurityHeaders (ResponseDecorator)
4. CORS
5. ErrorHandling
6. RequestSizeLimit
7. BearerAuth
8. Routing / dispatch
```

## Money (binding)

Integer cents only. No floats. No `DECIMAL` in SQLite test schemas.

## Tenant isolation (binding)

Every query on a tenant table includes `organization_id` in WHERE. Extract from JWT; never trust request body.

## Verification commands

```bash
composer check     # PHPUnit + PHPStan level 8 + CS-Fixer + OpenAPI
composer test      # PHPUnit only
composer analyse   # PHPStan only
composer cs        # CS-Fixer dry-run
composer cs:fix    # CS-Fixer auto-fix
composer openapi   # OpenAPI contract validation
```
