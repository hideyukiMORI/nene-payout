# ADR 0005 — Product Identity

Date: 2026-06-13
Status: Accepted

## Context

Naming must be consistent across repository, namespace, package, and UI.

## Decision

- Product name: **NeNe Payout**
- Repository: `hideyukiMORI/nene-payout`
- PHP namespace: `NenePayout\`
- Composer package: `hideyukimori/nene-payout`
- Port lane: 90** (API: 9000, Frontend: 5190, MySQL: 3400, phpMyAdmin: 9001) — fixed/unique, see `docs/development/local-ports.md`

## Consequences

All identifiers must match `docs/terms.md` exactly. Deviations block merge.
