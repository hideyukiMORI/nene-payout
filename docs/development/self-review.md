# Self-Review Checklist — NeNe Payout

Run the relevant checklist before creating a PR. Include the checklist name in the PR body.

> **Terminology gate (binding, every PR).** Every identifier and product/domain
> spelling must match [`../terms.md`](../terms.md) character-for-character. New or
> renamed identifiers update `terms.md` in the **same PR**. Typos and 表記ゆれ
> block merge — no exceptions (ADR 0017). Verify with `git grep -n "<identifier>"`.

> **Binding compliance gate.** Any change touching received invoices, vendors,
> payment execution, amounts, fees, tax fields, gateway integration, webhooks,
> document references, or retention **MUST** also run
> [`../review/compliance.md`](../review/compliance.md) (source of truth:
> [`../explanation/payment-compliance.md`](../explanation/payment-compliance.md)).
> If unsure whether a change has compliance impact, assume it does.

## backend-api

- [ ] Handler is thin: parse → build Input DTO → call UseCase → response only
- [ ] UseCase has no HTTP/DB knowledge and never calls the container
- [ ] Real NENE2 classes used (no invented objects) — see `nene2-runtime-reference.md`
- [ ] Path params read from `Router::PARAMETERS_ATTRIBUTE` (not `getAttribute('id')`)
- [ ] `organization_id` read from the resolved `RequestScopedHolder` (ADR 0018), never from request body/path/query; repos filter by it
- [ ] Constructor injection only; no service locator in UseCase/domain code
- [ ] Time via injected `ClockInterface`/`UtcClock` (no ambient `date()`)
- [ ] All amounts are integer cents (no floats); `amount`/`charge_amount`/`processing_fee` distinct
- [ ] Every tenant query includes `organization_id` in WHERE
- [ ] Error responses use Problem Details (`ProblemDetailsResponseFactory`); domain exceptions mapped at error boundary
- [ ] Validation errors use `ValidationException` + `ValidationError` (422)
- [ ] No raw SQL outside Repository; repos depend on `DatabaseQueryExecutorInterface`
- [ ] No raw card numbers / tokens / secrets logged or stored
- [ ] Audit: every mutating op + state transition records via `AuditRecorderInterface` (who / action / before / after) — ADR 0011
- [ ] Audit: mutation + audit write commit in **one transaction**; before/after via sanitized `*Response` presenters (no PAN/tokens/secrets)
- [ ] Audit: `audit_logs` treated as append-only (no UPDATE/DELETE); reads not audited
- [ ] OpenAPI updated and passes `composer openapi`
- [ ] `docs/terms.md` checked for all new identifiers
- [ ] Compliance gate run when applicable (`../review/compliance.md`)

## database

- [ ] Phinx migration added for new tables (`YYYYMMDDHHMMSS_*`) + schema snapshot
- [ ] Rollback defined (or documented why not)
- [ ] SQLite-compatible SQL (Tier A); no MySQL-only syntax in core
- [ ] ULID `id`, `created_at`/`updated_at` UTC, `organization_id` on tenant tables
- [ ] Financial tables: soft delete / void only; no hard `DELETE` (ADR 0013)
- [ ] Adapter integration test covers SQL + type casting + tenant filter

## payment-gateway

- [ ] Gateway interface used (`PaymentGatewayInterface`); never direct HTTP in UseCase
- [ ] Webhook signature verified before processing; handler idempotent
- [ ] Raw card numbers never reach Payout server (hosted-only, SAQ-A — ADR 0010)
- [ ] Gateway credentials stored via admin config (not hardcoded), never logged
- [ ] Connectivity check (疎通確認) works from admin panel
- [ ] Fee/refund/chargeback accounting not added without a 税理士-signed ADR (ADR 0015)

## frontend

Use the full, binding checklist: [`../review/frontend.md`](../review/frontend.md)
(FSD architecture, zero-tolerance placement, data flow, theming, Storybook,
security, testing — ADR 0019 / `frontend-standards.md`). Key reminders:

- [ ] FSD layers `app→pages→features→entities→shared`; slices via `index.ts` only; no cross-feature/upward imports
- [ ] Entity files in mandated paths; DTOs/`fetch`/query-keys not outside their files
- [ ] Read/write data flow honored; loading/empty/error/success explicit
- [ ] Visual values only in `shared/ui/theme/*`; semantic Tailwind utilities; no literals / arbitrary values
- [ ] All UI strings via `t(key)` (ja+en parity); errors mapped from Problem Details
- [ ] Money integer (format at view edge); UTC→JST; no PAN/secrets; tenant server-resolved
- [ ] mapper + feature-hook (MSW) tests; `npm run check --prefix frontend` passes

## docs

- [ ] Every identifier matches `docs/terms.md` exactly (no typos / 表記ゆれ) — ADR 0017
- [ ] New or renamed identifiers registered in `docs/terms.md` in this PR; old spelling removed everywhere
- [ ] Product/domain names use canonical spelling (`NeNe Payout`, `NENE2`, …)
- [ ] ADR created when an architectural decision is made
- [ ] `docs/todo/current.md` updated if phase status changed
