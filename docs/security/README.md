# Security posture — NeNe Payout

Design-level record of the **authentication and authorization controls** in place
in NeNe Payout (self-hosted vendor-invoice card-payment platform) and pointers to
the automated tests that guard them. This is an internal maintainer document, not a
third-party penetration test. Live-fire assessment (see the fleet precedent in NeNe
Vault's `docs/security/`) is future work (T3 L3/L4) pending maintainer go-ahead.

Scope note: this directory documents **controls that are implemented and
verified**. It deliberately does not enumerate un-remediated weaknesses or
exploitation detail — the repository is public. Working notes for hardening still in
flight are tracked out-of-band with the fleet hub.

## Payment-compliance baseline (why the surface is small)

Payout is **software only**: all regulated money movement is delegated to the
licensed payment gateway (ADR 0009), and the application is **SAQ-A — it never
receives, stores, or transmits a PAN / cardholder data** (ADR 0010). The card entry
happens on the gateway's own surface. This removes the single highest-value target
from the application's attack surface by design; see
[`../explanation/payment-compliance.md`](../explanation/payment-compliance.md).

## Authentication

| Control | Where | Guarded by |
|---|---|---|
| Bearer token required on every non-bypass path; **fails closed `401`** when absent/malformed (bypass: health, login, webhooks, widget). | `src/Auth/BearerAuthMiddleware` | `tests/Auth/BearerAuthMiddlewareTest` |
| Local bearer-token verification via the fleet verifier. | `src/Auth/AuthServiceProvider` → `Nene2\Auth\LocalBearerTokenVerifier` | `tests/Auth/*`, `tests/Support/AuthContextTest` |
| JWT secret resolution **hard-fails in production** on the dev-secret opt-in — no weak-secret fallback in prod (dev secret only in local/test behind `NENE2_ALLOW_DEV_SECRET`). | `src/Auth/AuthServiceProvider` → fleet `Nene2\Auth\GuardedJwtSecretResolver` (#140/#141, #142/#143) | fleet-level (`GuardedJwtSecretResolver`) |
| Token stored in **`sessionStorage`** (tab-scoped, cleared on tab close) — **never `localStorage`** — to shrink the token's exposure window under XSS (#152/#153). One store instance is the single source of truth for get/set/clear. | `frontend/src/shared/api/auth-token.ts` (`createSessionTokenStore`, key `nene_payout_token`) | `frontend/…/shared/api/auth-token.test.ts`; `…/authenticate/ui/SignInPanel.test.tsx` (*token never in `localStorage`*) |
| Bearer mirrored onto both `Authorization` and `X-Authorization` (host-stripping proxy fallback, opt-in #165); no auth headers sent when signed out. | `frontend/src/shared/api/client.ts` | `frontend/…/shared/api/client.test.ts`; `tests/Http/AuthorizationHeaderFallbackE2ETest` |

## Authorization

Role-based capabilities (mirror of `src/Auth/Role.php` / `Capability.php`) drive
**UI visibility only** — the API stays the source of truth. Every gate defaults
**fail-closed**: an unknown/absent role grants nothing.

| Control | Where | Guarded by |
|---|---|---|
| Backend capability gate per route; unauthorized role is rejected. | `src/Auth/CapabilityMiddleware`, `CapabilityResolver`, `Capability` (7 capabilities) | `tests/Auth/CapabilityMiddlewareTest`, `CapabilityResolverTest`, `RoleTest` |
| Multi-tenant org scoping resolved per request (no cross-tenant leakage). | `src/Organization/Resolution/OrgResolverMiddleware` | `tests/Organization/Resolution/OrgResolverMiddlewareTest` |
| Frontend route guard fails closed: no token → `/login`. | `frontend/src/app/auth-gate.tsx` | `frontend/…/app/auth-gate.test.tsx` |
| Frontend capability guard fails closed: role lacking the capability → `/forbidden`; an unresolved/loading session renders nothing (no content leak). | `frontend/src/app/require-capability.tsx` | `frontend/…/app/require-capability.test.tsx` |
| Session-claim mapping fails closed: an unrecognized wire role maps to `null` (= no capabilities). | `frontend/src/entities/session/mapper.ts` (`isRole` guard) | `frontend/…/session/mapper.test.ts`, `…/session/queries.test.ts` |

## Output & error surface

- The SPA renders exclusively through **React's default escaping**; there is **no
  `dangerouslySetInnerHTML` / `innerHTML` sink** anywhere in the application code
  (structural — no sink exists).
- API failures surface as **RFC 9457 Problem Details** mapped to a typed `AppError`;
  the transport guarantees a non-2xx response never reaches the SPA as raw HTML (no
  error-page HTML injection into the app). Guarded by
  `frontend/…/shared/api/errors.test.ts`, `…/shared/api/client.test.ts`.

## Embeddable widget

Payout ships an embeddable payment widget with its own request path and
authentication middleware (`src/Widget/WidgetAuthMiddleware`), separate from the
operator bearer path. Because the widget is a browser-embeddable surface and the
product is still evolving (Issue #122), its host-embedding / origin-trust hardening
is tracked as T3 groundwork with the fleet hub rather than enumerated here.

## Frontend attack-surface inventory

A per-surface map of the browser-facing attack surface (XSS / token-at-rest / authz
/ error channel / transport), the defense in place, and its test coverage lives in
[`frontend-attack-surface.md`](frontend-attack-surface.md). It is the T3 groundwork
sheet — an inventory of implemented controls and their coverage, not an exploitation
guide.

## Planned hardening (T3 roadmap)

Forward-looking, not yet landed:

- Automated **authorization-regression** coverage (a role × capability × route
  matrix) built on the existing fail-closed guards.
- A **response-header baseline** (e.g. CSP / frame-ancestors) for the served SPA and
  the embeddable widget.
- **Dependency audit** signal in CI (Dependabot is already wired for updates; an
  explicit audit gate is the next step).
- Widget host-embedding / origin-trust hardening (see above).
- Live-fire assessment with a disposable harness (fleet precedent: Vault
  `docs/security/harness/`), against self-owned isolated environments only — never a
  production host — pending maintainer decision (L3/L4).
