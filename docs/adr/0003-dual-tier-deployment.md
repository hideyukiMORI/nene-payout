# ADR 0003 — Dual-Tier Deployment

Date: 2026-06-13
Status: Accepted

## Context

Target operators include Japan SMB on shared hosting (Tier A) and IT teams using Docker/VPS (Tier B).

## Decision

Same PHP codebase supports both tiers. SQLite for Tier A (shared hosting), MySQL for Tier B (Docker). Environment-driven DB adapter selection via `DB_ADAPTER` env var. Inherits NENE2 ADR-0003 pattern.

## Consequences

- Broader operator reach without maintaining separate codebases
- SQLite-compatible SQL required in core (no MySQL-specific syntax)
- Tier A has limitations: no background workers, webhook endpoint must be HTTPS-accessible
