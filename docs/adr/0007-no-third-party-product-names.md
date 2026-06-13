# ADR 0007 — No Competitor Product Names in Repository Docs

Date: 2026-06-13
Status: Accepted (clarified 2026-06-13)

## Context

Naming **competitor** products in OSS repository documentation creates legal risk, implicit comparisons, and maintenance burden when those products change.

This restriction targets competitor and comparison naming. It does **not** apply to third-party services that NeNe Payout actually integrates with — most importantly payment gateways, whose names are unavoidable as adapter identifiers, class names, and configuration values.

## Decision

- Repository docs, README, ADRs, OpenAPI, and code comments must not name, reference, or compare **competitor** commercial services or products (feature comparisons, "alternative to X", marketing positioning against a rival).
- Services that Payout **integrates with** (payment gateways) may be named where technically necessary: gateway identifiers (`docs/terms.md §6`), adapter class names (`StripeGatewayAdapter`), admin configuration, and the docs that describe them.
- Official API documentation of an integrated service may be referenced by URL in comments when technically necessary.

## Consequences

- No competitor product names or comparisons in any file tracked by git
- Integrated payment gateway names are allowed and registered in `docs/terms.md §6`
- Gateway adapter code may reference official API documentation URLs in comments
- PR reviewers must reject any file introducing competitor product names or comparisons
