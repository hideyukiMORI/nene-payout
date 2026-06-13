# ADR 0018 — Request-Based Tenant Resolution

Date: 2026-06-13
Status: Accepted

## Context

ADR 0004 established multi-tenancy with `organization_id` on every tenant table
and stated that `org_id` is carried in the JWT and extracted in the Handler. As
we design for real deployments (Tier A single-tenant, NeNe Suite multi-tenant,
and an embeddable widget), the sibling product `nene-records` shows a cleaner,
proven model: resolve the tenant from the **request** and enforce isolation in
**repositories** via a request-scoped holder. The user chose to adopt the
`nene-records` model.

Coupling org selection to the JWT alone is limiting: the same user/token model
should work whether the org is addressed by subdomain, path prefix, a single-org
env, or a vanity domain; and the widget addresses its org by signed token, not by
a user JWT.

## Decision

- **Tenant context is resolved from the request**, not from the request body and
  not solely from a user token:
  - `OrgResolutionStrategyInterface` with `Subdomain` / `PathPrefix` / `Env`
    (single-tenant) / `CustomDomain` strategies, selected by `TENANT_RESOLUTION`
    (env/config). Config: `BASE_DOMAIN`, `ORG_SLUG`.
  - `OrgResolverMiddleware` resolves slug/custom-domain → `Organization`, checks
    `is_active`, stores `organization_id` in a shared
    `RequestScopedHolder<string>` (`Nene2\Http\RequestScopedHolder`), and sets
    `nene2.org.id` / `nene2.org.slug` request attributes. Infrastructure /
    superadmin / auth routes bypass resolution. Failures are Problem Details
    (`org-not-resolved` 404 / `org-not-found` 404 / `org-inactive` 403).
- **User identity and role come from authentication** (`BearerTokenMiddleware`),
  a concern separate from tenant resolution.
- **Isolation is enforced in repositories**: every tenant query filters
  `organization_id` from the holder; `organization_id` is never read from the
  body/path/query for scoping. Superadmin bypass is limited to explicit
  superadmin routes.
- The **widget** derives its org from its signed token (server-validated), not
  from host or a client parameter.

This **supersedes the resolution mechanism** described in ADR 0004 (org from
JWT). ADR 0004's other decisions (every tenant table has `organization_id`; role
set superadmin/admin/operator; superadmin cross-tenant) remain in force.

## Consequences

- One auth model works across subdomain, path, single-org, and vanity-domain
  deployments.
- Tenant scoping lives in one place (repositories + holder), reducing the chance
  of a forgotten filter compared with threading `org_id` through every signature.
- A new framework dependency on `RequestScopedHolder` and an `OrgResolverMiddleware`
  pipeline position must be wired in Phase 1.
- Binding design detail lives in `docs/explanation/multi-tenancy.md`.

## Related

- Supersedes (resolution only): `docs/adr/0004-multi-tenancy-and-roles.md`
- Design: `docs/explanation/multi-tenancy.md`
- Reference implementation: `../nene-records` `src/Organization/Resolution/`
