# ADR 0001 — Inherit NENE2 Governance

Date: 2026-06-13
Status: Accepted

## Context

NeNe Payout is part of the NeNe ecosystem. Establishing a separate governance model from scratch would be costly and inconsistent.

## Decision

Inherit NENE2 coding standards, workflow, middleware stack, DI pattern, validation, error responses, and tooling (PHPStan level 8, PHP-CS-Fixer, Phinx). See `docs/inheritance-from-nene2.md`.

## Consequences

- Consistent standards across the NeNe ecosystem
- NENE2 upstream docs are authoritative for framework behavior
- Local ADRs override NENE2 defaults only when product-specific needs require it
