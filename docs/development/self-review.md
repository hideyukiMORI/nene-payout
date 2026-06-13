# Self-Review Checklist — NeNe Payout

Run the relevant checklist before creating a PR. Include the checklist name in the PR body.

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
- [ ] `organization_id` taken from JWT claims (`nene2.auth.claims`), never request body
- [ ] Constructor injection only; no service locator in UseCase/domain code
- [ ] Time via injected `ClockInterface`/`UtcClock` (no ambient `date()`)
- [ ] All amounts are integer cents (no floats); `amount`/`charge_amount`/`processing_fee` distinct
- [ ] Every tenant query includes `organization_id` in WHERE
- [ ] Error responses use Problem Details (`ProblemDetailsResponseFactory`); domain exceptions mapped at error boundary
- [ ] Validation errors use `ValidationException` + `ValidationError` (422)
- [ ] No raw SQL outside Repository; repos depend on `DatabaseQueryExecutorInterface`
- [ ] No raw card numbers / tokens / secrets logged or stored
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

- [ ] React + TS + Vite; source in `frontend/`, build to `public_html/assets/` only
- [ ] Files grouped by feature/role; components named after their role
- [ ] All UI strings via i18n (no hardcoded Japanese/English)
- [ ] API calls via typed client only (`frontend/src/api`); no direct `fetch` in components
- [ ] Money integer end-to-end; UTC→JST conversion only at the view edge
- [ ] No secrets / PAN in frontend code or built assets
- [ ] `npm run check --prefix frontend` passes

## docs

- [ ] New identifiers registered in `docs/terms.md`
- [ ] ADR created when an architectural decision is made
- [ ] `docs/todo/current.md` updated if phase status changed
