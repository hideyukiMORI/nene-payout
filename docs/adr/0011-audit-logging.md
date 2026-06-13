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

The full binding design is [`docs/explanation/audit-logging.md`](../explanation/audit-logging.md).

## Decision

- A dedicated `audit_logs` table records one row per mutating operation:
  `actor_user_id`, `organization_id`, `action` (`{entity}.{verb}`),
  `entity_type`, `entity_id`, `before_json`, `after_json`, `request_id`,
  `created_at` (UTC). `actor_email` is resolved at read time, never stored.
- **Recording happens in the UseCase** via an `AuditRecorderInterface`. Handlers
  pass the actor user id from auth claims (`nene2.auth.claims`); the tenant
  `organization_id` comes from the resolved `RequestScopedHolder` (ADR 0018); the
  UseCase already holds the before state.
- **All create / update / void operations** and **every state transition**
  (including webhook-driven payment transitions `payment.succeeded` /
  `payment.failed` / `payment.refunded` / `payment.charged_back`) are recorded.
  Reads are not audited.
- **Before and after are both recorded** as **sanitized snapshots** built from
  the same `*Response` presenters used for API output, so card tokens, gateway
  API keys, webhook secrets, password hashes, and PAN-adjacent data are **never**
  written. The diff is derivable from the two snapshots.
- **The mutation and its audit record commit in a single transaction**
  (`DatabaseTransactionManagerInterface::transactional()` with the repository and
  recorder rebound to the transaction executor via factories). No best-effort,
  out-of-transaction audit.
- `audit_logs` is **append-only and immutable** (no UPDATE/DELETE), retained for
  the statutory period (ADR 0013).
- New domains record audit from the start.

## Consequences

- Uniform, compliance-aligned trail of who changed what, with before/after.
- Secrets excluded by reusing sanitized presenters.
- UseCases gain an `AuditRecorderInterface` dependency, an actor-id argument, and
  a transaction boundary that wraps mutation + audit.
- Atomicity is a decision here (not a follow-up): the two writes never diverge.

## Related

- Binding design: `docs/explanation/audit-logging.md`
- Binding: `docs/explanation/payment-compliance.md` §9
- Immutability / retention: `docs/adr/0013-payment-record-immutability-and-retention.md`
- Tenant resolution: `docs/adr/0018-request-based-tenant-resolution.md`
