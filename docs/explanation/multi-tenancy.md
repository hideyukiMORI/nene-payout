# Multi-Tenancy — Binding Design

**Status: binding.** NeNe Payout is multi-tenant by premise. This document defines
how a tenant (organization) is resolved, isolated, and authorized. It is modeled
on the `nene-records` implementation (request-based resolution + a request-scoped
org holder + repository-level enforcement) and adapted to Payout's identifiers
and compliance rules.

See: [ADR 0004](../adr/0004-multi-tenancy-and-roles.md),
[ADR 0018](../adr/0018-request-based-tenant-resolution.md),
[`payment-compliance.md`](./payment-compliance.md),
[`../development/nene2-runtime-reference.md`](../development/nene2-runtime-reference.md).

---

## 1. Model

- A **tenant is an `Organization`**. Every tenant-scoped row carries
  `organization_id` (ULID string). One installation may serve one organization
  (Tier A) or many (NeNe Suite).
- **Tenant context (which org) is resolved from the request** — not from the
  request body and not solely from a user token. **User identity and role** come
  from authentication (NENE2 `BearerTokenMiddleware`); the two concerns are
  separate (ADR 0018).
- Tenant isolation is **enforced in repositories**: every query on a tenant table
  filters by the resolved `organization_id`.

```text
request
  → OrgResolverMiddleware        resolve org → RequestScopedHolder<string> (org_id) + request attributes
  → BearerTokenMiddleware        authenticate user → claims (user id, role)
  → Handler                      authorize (Role/Capability), build Input DTO
    → UseCase                    business invariants
      → PdoXxxRepository         every query: ... WHERE organization_id = ?   (from the holder)
```

---

## 2. Organization entity

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key (`organization_id` elsewhere) |
| slug | string | URL-safe tenant key (subdomain / path segment); unique |
| name | string | Display name |
| custom_domain | string\|null | Optional vanity domain; unique when set |
| is_active | bool | Inactive orgs are rejected at resolution |
| created_at / updated_at | datetime | UTC (ADR 0012) |

Slug conflicts raise a named domain exception mapped to a stable Problem type.

---

## 3. Tenant resolution (binding)

Resolution is pluggable behind `OrgResolutionStrategyInterface` and selected by
the `TENANT_RESOLUTION` env var (overridable per-installation config). Strategies
return an org **slug or custom-domain identifier**, or `null` when they cannot
determine one.

| `TENANT_RESOLUTION` | Strategy | Resolves from | Use |
| --- | --- | --- | --- |
| `single` (default) | `EnvResolutionStrategy` | `ORG_SLUG` env | Tier A single-tenant / local dev |
| `subdomain` | `SubdomainResolutionStrategy` | `org1.<BASE_DOMAIN>` → `org1` | Suite multi-tenant |
| `path` | `PathPrefixResolutionStrategy` | `/<slug>/...` path prefix | Suite, no wildcard DNS |
| `custom_domain` | `CustomDomainResolutionStrategy` | full host → `custom_domain` lookup | vanity domains |

Config env: `TENANT_RESOLUTION`, `BASE_DOMAIN` (subdomain mode), `ORG_SLUG`
(single mode).

### `OrgResolverMiddleware` (binding behavior)

1. **Bypass** infrastructure/cross-tenant paths (no org needed): `/health`,
   auth routes, and superadmin organization-management routes pass through with
   org unset. Handlers on bypassed routes must not read the org holder.
2. `strategy->resolve()` → identifier; `null` → `404 org-not-resolved`.
3. `OrganizationRepository::findBySlug()` then `findByCustomDomain()`; not found
   → `404 org-not-found`.
4. `!is_active` → `403 org-inactive`.
5. Store `organization_id` in a shared **`RequestScopedHolder<string>`** and set
   request attributes `nene2.org.id` and `nene2.org.slug`.

All failures are RFC 9457 Problem Details (`ProblemDetailsResponseFactory`).

### Pipeline position

```text
ErrorHandler → RequestId → SecurityHeaders → CORS → RequestSizeLimit
  → BearerToken/ApiKey (authenticate)
  → OrgResolverMiddleware (resolve tenant)
  → CapabilityMiddleware (authorize)
  → Router / dispatch
```

---

## 4. Isolation enforcement (binding)

- Every tenant-scoped repository injects the shared
  `RequestScopedHolder<string> $orgId` (NENE2 `Nene2\Http\RequestScopedHolder`)
  and adds `AND organization_id = ?` with `$this->orgId->get()` to **every**
  read and write. No exceptions.
- `organization_id` is **never** taken from the request body or a path/query
  parameter for scoping — only from the resolved holder (ADR 0018).
- Writes set `organization_id` from the holder; clients cannot choose it.
- Cross-tenant access is only possible for **superadmin** on explicitly
  bypassed superadmin routes (§5).
- Unit tests use an in-memory repository double seeded with a fixed org; tenant
  leakage tests assert that another org's rows are never returned.

This satisfies `payment-compliance.md` tenant-isolation requirements and ADR 0004.

---

## 5. Roles & capabilities (binding)

Authentication yields a user with a `Role`. Authorization uses a `Capability`
enum checked by `CapabilityMiddleware` (and/or in the Handler). Roles match
[`pages.md`](./pages.md).

| Capability | superadmin | admin | operator |
| --- | --- | --- | --- |
| `ManageOrganizations` (cross-tenant) | ✓ | — | — |
| `ManageGatewaySettings` | ✓ | ✓ | — |
| `ManageVendors` | ✓ | ✓ | — |
| `ManageOrganizationSettings` | ✓ | ✓ | — |
| `RegisterInvoice` | ✓ | ✓ | ✓ |
| `InitiatePayment` | ✓ | ✓ | ✓ |
| `ViewPayments` | ✓ | ✓ | ✓ |

- **superadmin** is cross-tenant and used only for Suite orchestration / org
  management; it bypasses org-scope on explicitly designated routes.
- **admin** has full access **within its own organization**.
- **operator** creates invoices and initiates payments; **no settings**.

---

## 6. Widget tenant context (binding)

The embeddable widget is loaded on third-party sites and is **token-gated**
(see `pages.md` `/widget`). Its organization context comes from the **signed
widget token**, not from subdomain/host. The widget never selects an org from a
client-supplied parameter, and the token is validated server-side before any
payment flow. No PAN, no secrets in the widget (ADR 0010).

---

## 7. Audit & compliance

- Every audit record carries `organization_id` (ADR 0011).
- Tenant isolation is a compliance control: a financial record must never be
  visible or mutable across organizations.

---

## Non-goals

- Row-level security in the database engine (enforcement is in repositories).
- Sharing a database with sibling products (ADR 0002) — tenancy is within
  Payout's own DB only.
- Per-tenant schemas / separate databases per tenant (single shared schema,
  `organization_id`-scoped).

## Related

- Roles & tenancy decision: [ADR 0004](../adr/0004-multi-tenancy-and-roles.md)
- Resolution decision: [ADR 0018](../adr/0018-request-based-tenant-resolution.md)
- Runtime objects: [`../development/nene2-runtime-reference.md`](../development/nene2-runtime-reference.md)
- Reference implementation: `../nene-records` (`src/Organization/Resolution/`)
