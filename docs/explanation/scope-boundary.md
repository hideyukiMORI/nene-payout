# Scope Boundary — NeNe Payout

## What Payout owns

```
ReceivedInvoice   amount, vendor bank account, due date, PDF reference, status
PaymentRequest    card charge request, amount, gateway, initiated_at
PaymentResult     status, transferred_at, gateway_reference
VendorAccount     bank_code, branch_code, account_number, account_name (reusable)
```

## What Payout does NOT own

| Domain | Owner |
| --- | --- |
| Issued invoices (売掛金) | `nene-invoice` |
| Incoming deposit reconciliation | `nene-clear` |
| Long-term document archive | `nene-vault` |
| Full accounts payable ledger | Future scope |

## Integration boundary

Payout may **reference** sibling product entities via HTTP (read-only):

- Link a received invoice to a `nene-vault` document by URL
- Reference a vendor from `nene-invoice` client list by ID

Payout does **not** write to sibling databases.

## Payment gateway boundary

Card charging and bank transfer execution are delegated to the payment gateway.
Payout communicates via adapter interfaces:

```
PaymentGatewayInterface
  └── charge(ChargeRequest): ChargeResult
  └── verify(): VerifyResult
```

Raw card numbers never pass through Payout's server.
Card input is handled by the gateway's secure iframe (tokenization).
