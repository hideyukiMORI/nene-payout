# ADR 0002 — Separate from Sibling Products

Date: 2026-06-13
Status: Accepted

## Context

The NeNe ecosystem has multiple products covering different financial domains. Mixing domains in a single codebase or database creates coupling and unclear responsibility boundaries.

## Decision

NeNe Payout owns only: received invoice registration, vendor bank account management, payment execution, and payment history. It does not share a database with sibling products. Integration is HTTP read-only reference only.

## Consequences

- Each product can evolve independently
- No cross-product DB migrations
- Integration requires HTTP calls (acceptable latency for financial ops)
