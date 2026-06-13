# ADR 0011 — Audit Logging of All Mutating Operations

Date: 2026-06-13
Status: Accepted

## Context

`payment-compliance.md` §9 requires an audit trail for payment-sensitive actions.
A reviewer or auditor must be able to reconstruct the history of any vendor,
received invoice, or payment execution — including before/after state and the
actor — without scattered, ad-hoc logging. The sibling product `nene-invoice`
records audit at the UseCase layer, where both actor/tenant context and the
before/after entity state are available.

## Decision

- A dedicated `audit_logs` table records one row per mutating operation:
  `actor_user_id`, `organization_id`, `action` (`{entity}.{verb}`),
  `entity_type`, `entity_id`, `before_json`, `after_json`, `created_at`.
- **Recording happens in the UseCase** via an `AuditRecorder`. Handlers pass the
  actor user id from JWT claims; the UseCase already holds tenant context and the
  before state.
- **All create / update / void operations** and **every payment status
  transition** (including webhook-driven ones, e.g. `payment.succeeded`,
  `payment.failed`, `payment.refunded`, `payment.charged_back`) are recorded.
  Reads are not audited.
- **Before/after are sanitized snapshots** built from the same presenters used
  for API output, so card tokens, gateway API keys, webhook secrets, and any
  PAN-adjacent data are **never** written to the audit trail.
- New domains record audit from the start.

## Consequences

- Uniform, compliance-aligned trail of who changed what, with before/after.
- Secrets excluded by reusing sanitized presenters.
- UseCases gain an `AuditRecorder` dependency and an actor-id argument.
- Making the mutation + audit write atomic in one transaction is a planned
  follow-up.

## Related

- Binding: `docs/explanation/payment-compliance.md` §9
