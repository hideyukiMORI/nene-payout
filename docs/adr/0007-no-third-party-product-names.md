# ADR 0007 — No Third-Party Product Names in Repository Docs

Date: 2026-06-13
Status: Accepted

## Context

Naming competitor or partner products in OSS repository documentation creates legal risk, implicit comparisons, and maintenance burden when those products change.

## Decision

Repository docs, README, ADRs, OpenAPI, and code comments must not name, reference, or compare any third-party commercial services or products. Technical standards bodies (Stripe API docs, OpenAPI spec) may be referenced by URL only when technically necessary.

## Consequences

- No competitor product names in any file tracked by git
- Gateway adapter code may reference official API documentation URLs in comments
- PR reviewers must reject any file introducing third-party product names
