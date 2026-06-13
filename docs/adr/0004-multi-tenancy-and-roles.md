# ADR 0004 — Multi-Tenancy and Roles

Date: 2026-06-13
Status: Accepted (resolution mechanism superseded by ADR 0018)

## Context

NeNe Payout may be used by multiple organizations on a single installation (NeNe Suite multi-tenant mode) or as a single-tenant installation.

## Decision

Every tenant-scoped table includes `organization_id`. Roles: `superadmin` (cross-tenant) / `admin` / `operator`. User authentication uses the NENE2 `BearerTokenMiddleware` + `TokenVerifierInterface` pattern (local: `LocalBearerTokenVerifier`).

> **Tenant resolution mechanism — superseded by [ADR 0018](./0018-request-based-tenant-resolution.md).**
> The tenant (`organization_id`) is resolved from the **request**
> (subdomain / path / env / custom-domain) and held in a
> `RequestScopedHolder`, **not** from a JWT `org_id` claim. Repositories read it
> from the holder. The full design is [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md).

## Consequences

- Every repository query on a tenant table must include `organization_id` filter (from the resolved holder)
- Superadmin bypasses tenant scope (used for Suite orchestration only)
