# Coding Standards — NeNe Payout

Inherits [NENE2 coding standards](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/coding-standards.md). NENE2 conventions are **binding** (ADR 0016). This file records Payout-specific additions and overrides only. For exact framework classes and data flow, see [`nene2-runtime-reference.md`](./nene2-runtime-reference.md).

## Baseline

- PHP `>=8.4`
- `declare(strict_types=1)` in every PHP file; minimal file headers (no banners)
- PSR-12
- PHPStan level 8
- PHP-CS-Fixer
- Native types, enums, and small readonly DTOs at boundaries — not unstructured arrays
- Prefer immutable value objects / `readonly`; constructor injection for required deps
- No framework magic that hides control flow from tests, static analysis, or AI tools
- No service-locator (`$container->get()`) inside UseCases or domain objects
- PHPDoc only for public APIs, interfaces, extension points, and non-obvious rules — never repeat native types

## Architecture

```
src/{Domain}/
  Handler/       HTTP request handler — thin: parse → use-case → response
  UseCase/       Business logic — no HTTP/DB knowledge
  Repository/    RepositoryInterface + PdoXxxRepository
```

See [`backend-standards.md`](./backend-standards.md) for full layering rules.

## Money

All amounts are stored as **integer cents**. ¥1,000 = `100000`. No floats, no `DECIMAL` in SQLite tests.

## Token / credential security

- Never store raw card numbers — use gateway tokenization only
- Never log card tokens, API keys, or webhook secrets
- Hash sensitive tokens with SHA-256 before storage

## Cross-driver SQL

Repositories depend on `DatabaseQueryExecutorInterface` and write
SQLite-compatible SQL (Tier A) — no MySQL-only syntax in core (ADR 0003). NENE2
has no upsert helper; express upserts with explicit, driver-portable SQL inside
the `PdoXxxRepository`. See [`database-standards.md`](./database-standards.md).

## PHPDoc

Write PHPDoc only for public APIs, interfaces, extension points, and non-obvious business rules. Do not repeat native types.
