# ADR 0008 — Payment Compliance Is Non-Negotiable

Date: 2026-06-13
Status: Accepted

## Context

NeNe Payout executes credit-card payment of received vendor invoices on the
**payer side**. This touches Japanese payment-services law, consumption-tax /
qualified-invoice rules, and electronic-record retention law. To be credible to
accountants and tax professionals (士業), the product must be unable, by
construction, to put its operator into a non-compliant position. The sibling
product `nene-invoice` established the same principle on the receiver side via a
binding `accounting-compliance.md` and a professional sign-off gate.

## Decision

- `docs/explanation/payment-compliance.md` is **binding (non-negotiable)** and is
  the single source of truth for legal/accounting/tax adherence.
- Compliance takes precedence over UX, performance, and implementation
  convenience — every time.
- **No silent deviation.** Any departure from the binding rules — even temporary —
  requires an ADR **and** explicit sign-off by a tax/accounting/legal
  professional (税理士・会計士・弁護士) recorded in that ADR. No merge without it.
- Engineering is not the legal authority; the docs are engineering's binding
  *interpretation*, not legal advice.
- Every change with possible compliance impact runs `docs/review/compliance.md`
  and states impact in the PR.

## Consequences

- Compliance review is a gate, not a courtesy.
- Genuinely hard areas (fee/refund/chargeback accounting) are explicitly deferred
  pending professional sign-off (ADR 0015).
- Adds review overhead, accepted as the cost of professional credibility.
