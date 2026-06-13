# Scope Contract — NeNe Payout

This document is binding. Changes require an ADR.
Legal / accounting / tax rules are governed by the binding
[`payment-compliance.md`](./payment-compliance.md) (non-negotiable).

## GOAL

Enable businesses to pay received vendor invoices by credit card — keeping all
payment data on their own server — **as software that instructs and records
payments, delegating all regulated money movement to a licensed payment
gateway** (ADR 0009). Payout is not a payment, remittance, or financial service.

## DO

- Register and manage received invoices (lightweight — amount, vendor bank account, due date, PDF reference)
- Execute credit card payment that triggers a transfer to the vendor's bank account via payment gateway
- Provide an embeddable widget for integration into existing systems
- Support multiple payment gateways via adapter pattern (switchable from admin panel)
- Provide connectivity verification (疎通確認) from admin panel
- Record payment history and status — immutably and tamper-evidently — on the operator's own server (ADR 0013)
- Record (record & link only) the vendor registration number (T+13) and per-rate tax breakdown for the operator's input-tax-credit hand-off (ADR 0014)
- Keep an audit trail of every mutating operation and payment status transition (ADR 0011)
- Link to sibling products via HTTP reference only (no shared DB)

## DON'T

### Domain boundaries (sibling products)
- Issue quotes or invoices → [`nene-invoice`](https://github.com/hideyukiMORI/nene-invoice)
- Reconcile incoming bank deposits → [`nene-clear`](https://github.com/hideyukiMORI/nene-clear)
- Archive received documents for long-term retention → [`nene-vault`](https://github.com/hideyukiMORI/nene-vault)
- Manage full accounts payable (承認フロー, 買掛金分析) — future scope
- Provide payroll, expense reimbursement, or tax calculation

### Legal / regulated activity (delegated — ADR 0009)
- Hold, pool, escrow, or take custody of operator or vendor funds
- Perform 為替取引 / 資金移動 / 収納代行 itself — delegated to the licensed gateway
- Perform AML/KYC (取引時確認) itself, or claim to satisfy the operator's AML duty
- Describe Payout as a payment, remittance, or financial service in docs/UI/marketing

### Tax / accounting (record & link only — ADR 0014, 0015)
- Act as the legal retention store for qualified invoices, or compute / assert input tax credit (仕入税額控除)
- Present a registration-number format pass as proof of validity
- Implement fee / refund / chargeback **accounting** before a 税理士/会計士-signed ADR exists (ADR 0015)
- Claim to certify 下請法 / フリーランス新法 payment-deadline compliance

### Security
- Store raw credit card numbers (PAN) on the operator's server — hosted-only capture, SAQ-A (ADR 0010)

### Repository policy
- Name, reference, or compare **competitor** commercial services in repository docs (integrated payment gateway names are allowed) — [ADR 0007](../adr/0007-no-third-party-product-names.md)

## Boundary Summary

```
Received invoice → register in Payout → pay by card → payment gateway → vendor bank account
                                                   ↓
                                          payment record stored on own server
```

The payment execution (card charge + bank transfer) is delegated to the payment gateway.
Payout owns the invoice registration, payment initiation, and result recording.
