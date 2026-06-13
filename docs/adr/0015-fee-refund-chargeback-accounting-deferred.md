# ADR 0015 — Fee / Refund / Chargeback Accounting Deferred Pending Professional Sign-Off

Date: 2026-06-13
Status: Accepted

## Context

Card payment of an invoice introduces a **processing fee**, and the possibility
of **refunds** and **chargebacks**. How these map onto the recorded figures and
the operator's books carries **more compliance risk than the payment flow
itself**, and the correct treatment can differ by gateway contract:

- the consumption-tax character of the processing fee (課税 / 非課税),
- whether the vendor receives gross while the operator's card is charged
  gross + fee, or a net-of-fee model applies,
- how a refund reverses a recorded payment,
- how a chargeback is represented without mutating the original record.

These are accounting/tax determinations, not engineering choices.

## Decision

- The fee/refund/chargeback **accounting model MUST NOT be implemented** until it
  is reviewed and signed off by a **税理士 / 会計士**, recorded in a follow-up
  ADR. This ADR fixes the **constraint**, not the ledger design.
- Until then, Payout records the **gateway-reported amounts verbatim**
  (`amount`, `charge_amount`, `processing_fee`, and refund/chargeback events as
  additive linked records — ADR 0013) **without** asserting their tax character
  or bookkeeping classification.
- Net-of-fee vs. gross modelling, fee taxability, refund reversal logic, and
  chargeback accounting are **out of scope** until the signed-off ADR exists.

## Consequences

- The core payment flow (Phase 1) can proceed: record amounts, never interpret
  them.
- A clear, owned follow-up exists: fee/refund/chargeback accounting model +
  税理士 sign-off, updating `payment-compliance.md` §10.
- Premature, unreviewed accounting logic is prevented from merging.

## Related

- Binding: `docs/explanation/payment-compliance.md` §10
- Immutability/retention: `docs/adr/0013-payment-record-immutability-and-retention.md`
- Compliance gate: `docs/adr/0008-payment-compliance-non-negotiable.md`
