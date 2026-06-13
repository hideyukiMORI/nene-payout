# ADR 0014 — Input-Tax-Credit Evidence Boundary (Record & Link Only)

Date: 2026-06-13
Status: Accepted

## Context

NeNe Payout is the **payer**. For the operator to claim 仕入税額控除 (input tax
credit) under the qualified-invoice system (適格請求書等保存方式), the operator
must retain the vendor's qualified invoice plus a payment record. The question is
how far Payout participates. The user has chosen **record & link only** — Payout
must not become a tax engine or the legal evidence store.

## Decision

- Payout **MAY** record, against a received invoice, the vendor's
  **registration number** (`registration_number`, `^T[0-9]{13}$`) and a per-rate
  tax breakdown when the operator provides it.
- Registration number validation is **syntax only**: no existence check, no
  check-digit, no registry lookup. UI/docs **MUST NOT** present a format pass as
  proof of validity.
- Recorded tax rates are limited to **10% (1000 bps)** / **8% reduced (800 bps)**;
  any other rate requires an ADR.
- Payout **MUST NOT** be the legal retention store for the qualified invoice, nor
  the authority that determines deductibility. The invoice itself is retained via
  **nene-vault** / the operator's 電子帳簿保存法 retention; Payout stores a
  reference (`vault_document_url`).
- Payout **MUST NOT** compute or assert the operator's deductible tax amount.
  Recorded tax figures are descriptive copies for matching and hand-off only.

## Consequences

- Operators get useful matching data (registration number, per-rate breakdown)
  without Payout overreaching into tax computation or evidence custody.
- The authoritative deduction workflow lives with the operator + their 税理士 +
  the document archive, not in Payout.
- Cross-reference to nene-vault stays a read-only URL (ADR 0002).

## Related

- Binding: `docs/explanation/payment-compliance.md` §5
- Sibling integration: `docs/integrations/sibling-products.md`
