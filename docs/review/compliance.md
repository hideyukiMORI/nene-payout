# Payment, Legal & Tax Compliance Self-Review

**Binding.** Use for **any** change touching received invoices, vendors, payment
execution, amounts, fees, tax fields, gateway integration, webhook handling,
document references, or record retention. If unsure whether a change has
compliance impact, assume it does and run this list.

Source of truth: [`../explanation/payment-compliance.md`](../explanation/payment-compliance.md).
Do not delete items to pass. Mark `N/A` only when genuinely not applicable.

## Checklist

- [ ] Change reviewed against `docs/explanation/payment-compliance.md`; compliance impact stated in the PR.
- [ ] No funds custody: Payout never holds, pools, or escrows operator/vendor money (§2).
- [ ] No regulated activity introduced: no 為替取引 / 資金移動 / 収納代行 / AML-KYC performed by Payout itself; money movement stays delegated to the gateway (ADR 0009).
- [ ] Docs/UI do not describe Payout as a payment, remittance, or financial service.
- [ ] Card PAN never reaches the app/DB/server; hosted-only capture; operator stays at SAQ-A (ADR 0010).
- [ ] No card numbers, CVV, tokens, API keys, or webhook secrets in logs or audit trail.
- [ ] `amount` (vendor owed), `charge_amount` (card charged), `processing_fee` modelled and stored **separately**; no silent `charge_amount == amount` assumption (§4).
- [ ] Registration number treated as **syntax-only** (`^T[0-9]{13}$`); no UI/doc implies it proves existence/validity (§5).
- [ ] Recorded tax rates limited to 10% / 8%; any other rate carries an ADR.
- [ ] Payout does not claim to be the qualified-invoice retention store or to compute input tax credit; evidence linked via `vault_document_url` (ADR 0014).
- [ ] All money is integer minimum currency units; no float/DECIMAL in DB, JSON, or tests (§6).
- [ ] Monetary/fee/tax figures computed once in the UseCase; API and stored record do not recalculate independently.
- [ ] Payment records immutable on terminal state; refund/chargeback as new linked records, never in-place mutation (ADR 0013).
- [ ] No hard delete of financial records (soft delete / void only); no auto-purge before the statutory period (7y, up to 10y).
- [ ] Statutory dates correct independent of host timezone: UTC storage, JST display/derivation (ADR 0012).
- [ ] Audit trail recorded for every mutating operation and payment status transition, with sanitized before/after (ADR 0011).
- [ ] Subcontractor/freelancer deadline rules: `due_date` recorded but no claim of 下請法 / フリーランス新法 compliance (§8).
- [ ] Fee/refund/chargeback **accounting** not implemented without a 税理士/会計士-signed ADR; until then gateway amounts recorded verbatim (§10, ADR 0015).
- [ ] Any deviation from the binding rules carries an ADR with tax/accounting/legal professional sign-off.
