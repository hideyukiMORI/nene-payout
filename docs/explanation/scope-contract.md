# Scope Contract — NeNe Payout

This document is binding. Changes require an ADR.

## GOAL

Enable businesses to pay received vendor invoices by credit card,
with all payment data stored on their own server.

## DO

- Register and manage received invoices (lightweight — amount, vendor bank account, due date, PDF reference)
- Execute credit card payment that triggers a transfer to the vendor's bank account via payment gateway
- Provide an embeddable widget for integration into existing systems
- Support multiple payment gateways via adapter pattern (switchable from admin panel)
- Provide connectivity verification (疎通確認) from admin panel
- Record payment history and status on the operator's own server
- Link to sibling products via HTTP reference only (no shared DB)

## DON'T

- Issue quotes or invoices → [`nene-invoice`](https://github.com/hideyukiMORI/nene-invoice)
- Reconcile incoming bank deposits → [`nene-clear`](https://github.com/hideyukiMORI/nene-clear)
- Archive received documents for long-term retention → [`nene-vault`](https://github.com/hideyukiMORI/nene-vault)
- Manage full accounts payable (承認フロー, 買掛金分析) — future scope
- Provide payroll, expense reimbursement, or tax calculation
- Store raw credit card numbers on the operator's server (PCI DSS non-compliant)
- Name, reference, or compare **competitor** commercial services in repository docs (integrated payment gateway names are allowed) — [ADR 0007](../adr/0007-no-third-party-product-names.md)

## Boundary Summary

```
Received invoice → register in Payout → pay by card → payment gateway → vendor bank account
                                                   ↓
                                          payment record stored on own server
```

The payment execution (card charge + bank transfer) is delegated to the payment gateway.
Payout owns the invoice registration, payment initiation, and result recording.
