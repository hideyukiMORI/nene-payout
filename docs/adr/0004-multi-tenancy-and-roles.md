# ADR 0004 — Multi-Tenancy and Roles

Date: 2026-06-13
Status: Accepted

## Context

NeNe Payout may be used by multiple organizations on a single installation (NeNe Suite multi-tenant mode) or as a single-tenant installation.

## Decision

Every tenant-scoped table includes `organization_id`. JWT carries `org_id` claim. Roles: `superadmin` (cross-tenant) / `admin` / `operator`. Inherits NENE2 `BearerTokenMiddleware` + `TokenVerifierInterface` pattern (local: `LocalBearerTokenVerifier`).

## Consequences

- Every repository query on a tenant table must include `organization_id` filter
- Superadmin bypasses tenant scope (used for Suite orchestration only)
