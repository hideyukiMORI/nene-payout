# Roadmap — NeNe Payout

## Phase 0 — Governance and product design (current)

- [x] Governance bootstrap (AGENTS.md, CLAUDE.md, scope-contract, ADRs 0001–0007)
- [x] Domain model and terms registry
- [x] OpenAPI skeleton
- [x] Port registry update (nene-playbook)
- [x] docs/explanation/ full set (requirements, features, pages, glossary, domain-model, product-vision, scope-contract, scope-boundary)
- [x] docs/development/ full set (coding-standards, naming-conventions, backend-standards, nene2-compliance, commit-conventions, self-review)
- [x] Payment/legal/tax compliance foundation (binding payment-compliance.md, ADRs 0008–0015, review/compliance.md)
- [x] NENE2 coding conventions binding (ADR 0016, nene2-runtime-reference, database-standards, frontend-standards)
- [x] Terminology single source of truth + zero-typo enforcement (terms.md, ADR 0017)
- [x] Multi-tenancy design (request-based resolution + RequestScopedHolder; multi-tenancy.md, ADR 0018, ADR 0004 revised)
- [x] Audit logging design (all ops, before/after, atomic; audit-logging.md, ADR 0011 upgraded)
- [x] i18n design (ja/en message catalogs, instant switch; i18n.md)
- [x] Frontend architecture — strict FSD + mandated stack (frontend-standards.md, review/frontend.md, ADR 0019)
- [x] API surface + OpenAPI contract (docs/api/endpoints.md, docs/openapi/openapi.yaml — full surface)
- [x] GitHub repository created and initial commit pushed
- [x] Issue #1 created

## Phase 1 — Core payment API

All Phase 1 work is bound by `docs/explanation/payment-compliance.md` and must
pass `docs/review/compliance.md`.

- [x] NENE2 runtime scaffold (front controller, RuntimeServiceProvider, `GET /health`, composer check green) — Issue #26
- [x] Multi-tenant runtime: `Organization` + tenant resolution (`OrgResolverMiddleware` + strategies) → `RequestScopedHolder` (ADR 0018) — Issue #28
- [x] User auth (`BearerAuthMiddleware` + `LocalBearerTokenVerifier`, login/me) + `Role`/`Capability` + `CapabilityMiddleware` (users migration) — Issue #30
- [x] Vendor management CRUD (audited, 1-tx, soft-deactivate, registration_number record & link only) — Issue #34
- [x] ReceivedInvoice CRUD (audited, pending-only edit, void semantics, tax breakdown record-only) — Issue #36
- [x] ReceivedInvoice PDF upload (multipart, local storage, audited) — Issue #38
- [x] Payment gateway adapter interface (`PaymentGatewayInterface` + ChargeRequest/Result, stub adapter) — Issue #40 (instruction only; no PAN — ADR 0009, 0010)
- [ ] Stripe adapter (hosted charge + verify)
- [x] PaymentExecution create + invoice→processing (initiate, audited, 1-tx) — Issue #40 (immutable terminal records — ADR 0013)
- [x] Audit foundation (`Ulid`, `AuditRecorder`, audit_logs migration, `GET /api/v1/audit-logs`); mutation+audit transaction wiring applied per resource — ADR 0011 — Issue #32
- [ ] UTC storage / JST display bootstrap (ADR 0012)
- [ ] Webhook handler (payment result from gateway; signature-verified, idempotent)
- [ ] OpenAPI contract validation (composer openapi)

## Compliance follow-ups (gated)

- [ ] Fee / refund / chargeback accounting model + **税理士/会計士 sign-off** (ADR 0015 → follow-up ADR), then implement
- [ ] Launch gateway selection ADR (licensed/contracted entity — ADR 0009)

## Phase 2 — Admin UI + widget

- [x] FSD scaffold + tooling (strict ESLint boundaries, shared/ui theme + API client, app providers/router, entities/vendor + manage-vendors slice with MSW tests — Issue #50)
- [ ] React admin UI (remaining screens: received invoice list, payment history; forms via RHF + zod)
- [ ] Embeddable payment widget (script tag embed)
- [ ] CSS variable customization
- [ ] Admin panel: gateway configuration + connectivity check (疎通確認)
- [x] ja / en i18n foundation (shared/i18n catalogs, instant switch, key-parity test — Issue #44); screens pending

## Phase 3 — Tier A deployment

- [ ] Web installer (shared hosting)
- [ ] Release ZIP
- [ ] Operator guide

## Phase 4 — Extended gateways + integrations

- [ ] GMO Payment Gateway adapter
- [ ] nene-suite integration (NENE_SUITE_MODE)
- [ ] nene-vault document link
- [ ] nene-invoice vendor cross-reference

---

Last updated: 2026-06-13
