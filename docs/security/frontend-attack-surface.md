# Frontend attack-surface inventory — NeNe Payout

A one-sheet map of the browser-facing attack surface of the Payout SPA (and its
embeddable widget), the control in place for each, and its automated coverage. This
is **T3 groundwork**: an inventory of implemented defenses and where they are tested
— not an exploitation guide and not a penetration test. Un-landed hardening is
summarized under *Planned* (and tracked in detail out-of-band with the fleet hub,
per the public-repo policy).

Verdict legend: **✅ control in place + tested** · **▫️ control in place, dedicated
test planned** · **→ T3** (planned hardening, see [`README.md`](README.md)).

## Surface map

| Surface | Vector | Control in place | Coverage |
|---|---|---|---|
| **DOM rendering** | Stored/reflected XSS via app-rendered content | React default escaping; **no `dangerouslySetInnerHTML` / `innerHTML` sink** in app code | ✅ (structural — no sink exists) |
| **Cardholder data** | PAN theft from the app | **App never handles a PAN** (SAQ-A, ADR 0010); card entry is on the gateway surface | ✅ (structural — no PAN in app) |
| **Token at rest (browser)** | Token theft widening via persistent storage | Bearer token in `sessionStorage` only (tab-scoped, cleared on close); **never `localStorage`** | ✅ `shared/api/auth-token.test.ts`; `SignInPanel.test.tsx` (*token never in `localStorage`*) |
| **Route authz** | Reaching a gated view unauthenticated | Fail-closed guard `AuthGate` → `/login` | ✅ `app/auth-gate.test.tsx` |
| **Capability authz** | Reaching a view above one's role | Fail-closed guard `RequireCapability` → `/forbidden`; loading session renders nothing | ✅ `app/require-capability.test.tsx` |
| **Session claims** | Privilege inference from a malformed/legacy session | Mapper fails closed: unknown wire role → `role: null` (no capabilities) | ✅ `session/mapper.test.ts`, `session/queries.test.ts` |
| **API error channel** | HTML/error-page injection through the fetch layer | RFC 9457 Problem Details → typed `AppError`; transport never surfaces non-2xx as raw HTML | ✅ `shared/api/errors.test.ts`, `client.test.ts` |
| **Auth header transport** | Token omission / leakage across the proxy boundary | Bearer mirrored to `Authorization` + `X-Authorization`; no headers when signed out | ✅ `shared/api/client.test.ts` |
| **Locale preference** | Injection via persisted UI preference | Only a non-sensitive locale string (`nene-payout-locale`) is persisted; resolved through an allow-list, never used as a sink | ✅ `shared/i18n/locales.test.ts`, `i18n-context.test.tsx` |
| **Embeddable widget** | Host-embedding / origin trust of the mounted widget | Dedicated `WidgetAuthMiddleware` path (backend) | → T3 (origin-trust / frame-ancestors; widget still evolving, #122) |
| **Authz regression** | Silent privilege drift as routes/roles evolve | Fail-closed guards above are the foundation | → T3 (role × capability × route matrix) |
| **Supply chain** | Vulnerable transitive dependency | Lockfile-pinned installs; Dependabot updates wired | → T3 (explicit audit gate in CI) |
| **Transport hardening** | Browser-side hardening headers for the served SPA/widget | — | → T3 (response-header / CSP baseline) |

## Notes

- The **fail-closed default** is the through-line: unauthenticated, under-privileged,
  and malformed-session cases all resolve to the least-privilege outcome
  (login / forbidden / no capabilities), and each has a regression test.
- The **SAQ-A boundary** (no PAN in the app, ADR 0010) and the gateway delegation
  (ADR 0009) keep the highest-value asset off the browser surface entirely.
- Coverage above is largely the **T1-lite** result (auth-token / errors / AuthGate /
  session entity — Issues #222–#225). The `→ T3` rows are the next investment; their
  sequencing and any live-fire scope sit with the fleet hub and await the maintainer
  decision on L3/L4.
- This sheet lists surfaces and **controls**, deliberately not un-remediated gaps —
  the repo is public. The sharp gap list is the hub's internal T3 working note.
