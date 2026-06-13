# Self-Review Checklist — NeNe Payout

Run the relevant checklist before creating a PR. Include the checklist name in the PR body.

## backend-api

- [ ] Handler is thin: parse → use-case → response only
- [ ] UseCase has no HTTP/DB knowledge
- [ ] All amounts are integer cents (no floats)
- [ ] Every tenant query includes `organization_id` in WHERE
- [ ] Error responses use Problem Details (`ProblemDetailsResponseFactory`)
- [ ] Validation errors use `ValidationException` + `ValidationError`
- [ ] No raw SQL outside Repository
- [ ] No raw card numbers logged or stored
- [ ] OpenAPI updated and passes `composer openapi`
- [ ] `docs/terms.md` checked for all new identifiers

## payment-gateway

- [ ] Gateway interface used (never direct HTTP calls in UseCase)
- [ ] Webhook signature verified before processing
- [ ] Raw card numbers never reach Payout server
- [ ] Gateway credentials stored via admin config (not hardcoded)
- [ ] Connectivity check (疎通確認) works from admin panel

## frontend

- [ ] All UI strings via i18n (no hardcoded Japanese/English)
- [ ] API calls via typed client only
- [ ] No secrets in frontend code

## docs

- [ ] New identifiers registered in `docs/terms.md`
- [ ] ADR created when an architectural decision is made
- [ ] `docs/todo/current.md` updated if phase status changed
