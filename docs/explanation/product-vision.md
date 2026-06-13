# Product Vision — NeNe Payout

## One-line summary

Pay your vendors by card, keep your data.

## Problem

Businesses receive invoices from vendors who only accept bank transfers.
Paying by card is desirable for cash flow management (deferred settlement),
but existing solutions require sending financial data to external services,
creating vendor lock-in and data sovereignty concerns.

## Solution

NeNe Payout is a self-hosted OSS that bridges the gap:
the business pays by card, the payment gateway transfers funds to the vendor by bank transfer,
and all invoice and payment records stay on the operator's own server.

## Target users

- Japan SMB operators who want to manage cash flow without relying on external services
- IT teams and SaaS providers who want to embed payment capability into existing systems
- Operators who require financial data to remain on their own infrastructure (compliance, audit)

## Value proposition

| Dimension | Value |
| --- | --- |
| Data ownership | All invoice and payment data on own server |
| Design freedom | Full CSS customization; embeds seamlessly into any system |
| No lock-in | Swap payment gateways from admin panel |
| Cost | Server cost only; no per-transaction platform fee beyond gateway |
| Self-hosted | Runs on shared hosting (Tier A) or Docker/VPS (Tier B) |

## Relationship to NeNe ecosystem

NeNe Payout complements the existing NeNe suite:

```
NeNe Invoice  → issue invoices, receive payments
NeNe Clear    → reconcile incoming deposits
NeNe Vault    → archive received documents
NeNe Payout   → pay received invoices by card  ← this product
```

Together with NeNe Suite, these products cover the full financial operations cycle
for Japan SMB on self-hosted infrastructure.
