# ADR 0009 — Delegate All Regulated Money Movement to the Payment Gateway

Date: 2026-06-13
Status: Accepted

## Context

The product charges the operator's card and causes funds to reach the vendor's
bank account. In Japan, moving money on behalf of others can constitute 為替取引
and require a 資金移動業 / banking license; collection on behalf (収納代行) and
AML/CFT identity verification (犯罪収益移転防止法の取引時確認) are likewise
regulated activities. NeNe Payout is self-hosted OSS run by ordinary SMB
operators — it cannot and must not carry those licenses or obligations.

The user has chosen **full delegation**: the regulated money movement is the
licensed payment gateway's function; Payout is software that instructs and
records.

## Decision

- Payout's role is limited to: recording invoices/vendor accounts, presenting a
  gateway-hosted payment flow, sending a charge **instruction** via
  `PaymentGatewayInterface`, and recording the gateway's result.
- Payout **MUST NOT** hold, pool, escrow, or take custody of funds.
- Payout **MUST NOT** itself perform 為替取引, 資金移動, 収納代行, or 取引時確認
  (AML/KYC). These belong to the gateway (and, for KYC, the operator).
- Docs/UI/marketing **MUST NOT** describe Payout as a payment, remittance, or
  financial service.
- Any feature that would route money through Payout's own server/account, or
  otherwise place Payout in a licensed-activity position, requires a new ADR with
  **legal sign-off** before any code.

## Consequences

- Payout stays clearly outside 資金決済法 / 銀行法 licensing scope.
- The gateway choice must be a licensed/contracted entity that performs the
  regulated functions; adapter selection is a separate ADR.
- Some flows (e.g. status of an in-flight transfer) are only as observable as the
  gateway's API/webhooks allow — accepted.

## Related

- Binding: `docs/explanation/payment-compliance.md` §2
- Product separation: `docs/adr/0002-separate-from-sibling-products.md`
