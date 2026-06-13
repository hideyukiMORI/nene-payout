# Roadmap — NeNe Payout

## Phase 0 — Governance and product design (current)

- [ ] Governance bootstrap (AGENTS.md, CLAUDE.md, scope-contract, ADRs 0001–0007)
- [ ] Domain model and terms registry
- [ ] OpenAPI skeleton
- [ ] Port registry update (nene-playbook)

## Phase 1 — Core payment API

- [ ] NENE2 runtime scaffold (health, OpenAPI endpoint)
- [ ] Multi-tenant auth (organization + JWT, inherits NENE2 BearerAuth)
- [ ] Vendor management CRUD
- [ ] ReceivedInvoice CRUD (registration, PDF upload)
- [ ] Payment gateway adapter interface
- [ ] Stripe adapter (charge + verify)
- [ ] PaymentExecution create / status update
- [ ] Webhook handler (payment result from gateway)
- [ ] OpenAPI contract validation (composer openapi)

## Phase 2 — Admin UI + widget

- [ ] React admin UI (received invoice list, vendor list, payment history)
- [ ] Embeddable payment widget (script tag embed)
- [ ] CSS variable customization
- [ ] Admin panel: gateway configuration + connectivity check (疎通確認)
- [ ] ja / en UI

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
