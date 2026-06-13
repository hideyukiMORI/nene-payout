# Coding Standards — NeNe Payout

Inherits [NENE2 coding standards](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/coding-standards.md). This file records Payout-specific additions and overrides only.

## Baseline

- PHP `>=8.4`
- `declare(strict_types=1)` in every PHP file
- PSR-12
- PHPStan level 8
- PHP-CS-Fixer

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

## Cross-driver upsert

Use `DbUpsert::run()` from NENE2. Do not write driver-specific SQL by hand.

## PHPDoc

Write PHPDoc only for public APIs, interfaces, extension points, and non-obvious business rules. Do not repeat native types.
