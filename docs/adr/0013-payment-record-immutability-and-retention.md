# ADR 0013 — Payment Record Immutability, Void, and Retention

Date: 2026-06-13
Status: Accepted

## Context

Payment records are financial and electronic-transaction (電子取引) data. Under
電子帳簿保存法 and corporate-tax bookkeeping rules, they must be retained and
tamper-evident. An auditor must be able to trust that a recorded payment was not
silently altered or erased.

## Decision

- **Immutability.** Once a `PaymentExecution` reaches a terminal state
  (`succeeded` / `failed`), its amounts, `gateway_reference`, timestamps, and
  status history **MUST NOT** be edited or deleted. Later events (refund,
  chargeback) are recorded as **new linked records / status transitions**, never
  by mutating the original.
- **No hard delete.** Received invoices, payment executions, and the vendor
  account snapshot used in a payment use **soft delete / void** semantics; a
  voided record is recorded as voided, not erased.
- **Tamper-evident.** A stored record is never silently mutated; corrections
  produce a new versioned/linked record.
- **Retention.** Financial records and electronically received invoice data are
  retained for the statutory period — **in general 7 years, up to 10 years** in
  loss-carryforward situations. The product **MUST NOT** auto-purge financial
  records before that period; operators are warned before any destructive action.

## Consequences

- Schema carries soft-delete / void columns and a status-transition history for
  payments; no destructive `DELETE` on financial tables.
- Refund/chargeback modelling is additive (new records), aligning with ADR 0015.
- Storage grows with history; accepted for compliance.

## Related

- Binding: `docs/explanation/payment-compliance.md` §7
