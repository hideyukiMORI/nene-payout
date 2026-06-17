# ADR 0020 — Launch Payment Gateway Selection: Stripe (SAQ-A Hosted)

Date: 2026-06-17
Status: Proposed (awaiting legal sign-off — ADR 0009)

## Context

ADR 0009 delegates **all regulated money movement** to a licensed/contracted
gateway and requires that the gateway **selection** be a **separate ADR with
legal sign-off before any adapter code**. Phase 1 currently ships only a
`StubGatewayAdapter` (Issue #40); no production charge can occur until a launch
gateway is chosen under that gate.

Constraints that any launch gateway must satisfy:

- **ADR 0009** — the gateway (and, for KYC, the operator) performs the regulated
  functions (為替取引 / 資金移動 / 収納代行 / 取引時確認). Payout only instructs
  and records; it never holds, pools, escrows, or takes custody of funds.
- **ADR 0010** — SAQ-A hosted-only capture: the card PAN must never reach the
  application, its database, or the operator's server. Only gateway-hosted
  redirect or processor-hosted iframe (tokenization) is permitted.
- **ADR 0013** — settlement results are recorded immutably as additive, linked
  records; no mutation of the original.
- **ADR 0015** — the fee/refund/chargeback **accounting model** stays deferred
  until 税理士/会計士 sign-off; amounts are recorded verbatim, never interpreted.
- **ADR 0007** — names of gateways Payout integrates with are permitted where
  technically necessary (identifier `stripe` is registered in `docs/terms.md §6`;
  adapter class `StripeGatewayAdapter`).

## Decision

Select **Stripe** as the launch payment gateway adapter, integrated strictly
within the constraints above. This ADR fixes the **selection and its integration
constraints**, not the ledger or any code.

- **Hosted-only capture (ADR 0010).** Card entry uses a Stripe-hosted flow
  (Checkout / hosted payment page, or a processor-hosted element). The PAN never
  reaches Payout's server or database. Payout persists only opaque references
  (session id, payment-intent id → `gateway_reference`) and webhook payloads —
  never PAN, never CVV.
- **Instruct-and-record only (ADR 0009).** `PaymentGatewayInterface.createCharge`
  sends a charge **instruction**; funds move under Stripe's regulated function.
  Payout takes **no custody** of funds and performs no 為替/収納代行/取引時確認.
  KYC ownership stays with Stripe and the operator.
- **Webhook-driven result reflection.** Success/failure settlement arrives via
  Stripe webhooks and is recorded as additive, immutable records (ADR 0013).
  Gateway-reported amounts are stored **verbatim**; no fee/refund/chargeback
  accounting interpretation is performed (ADR 0015).
- **Secret handling (ADR 0010).** Gateway API keys and webhook signing secrets
  are never logged; sensitive tokens are hashed (SHA-256) where storage is
  required; secret values are never written to audit snapshots
  (`gateway_settings.updated`, `docs/terms.md §10`).
- **Naming (ADR 0007).** Adapter identifier `stripe` (`docs/terms.md §6`),
  class `StripeGatewayAdapter`; official Stripe API docs may be referenced by URL
  in code comments where technically necessary.

Per **ADR 0009, no adapter or money-movement code merges while this ADR is
`Proposed`.** Implementation (slice 9: `StripeGatewayAdapter` → webhooks →
`gateway-settings` + 疎通確認) begins only after this ADR is **signed off (legal)
and moved to `Accepted`**. Fee/refund/chargeback accounting remains separately
gated by ADR 0015.

## Open items required before `Accepted`

1. **Legal sign-off (ADR 0009):** written confirmation that the contracted Stripe
   entity performs the regulated money-movement functions for the operator's
   jurisdiction, and that Payout's instruct-and-record role stays outside
   資金決済法 / 銀行法 licensing scope.
2. **Contracted product + SAQ-A eligibility (ADR 0010):** the specific Stripe
   product used for capture (e.g. Checkout) is confirmed and is SAQ-A eligible.
3. **KYC boundary:** the operator's 取引時確認 (KYC) ownership is documented; Payout
   performs none of it.

## Consequences

- Once `Accepted`, slice 9 can implement the Stripe adapter, webhook handling, and
  `gateway-settings`, all within SAQ-A and ADR 0009 limits.
- Until sign-off, `StubGatewayAdapter` remains the only wired gateway and
  production charging stays disabled.
- Fee/refund/chargeback accounting is still blocked by ADR 0015 (a separate
  follow-up ADR with 税理士/会計士 sign-off).
- A second adapter (`gmo_pg`, `docs/terms.md §6`) would require its own selection
  ADR under the same gate.

## Related

- Gate: `docs/adr/0009-delegate-money-movement-to-gateway.md`
- SAQ-A: `docs/adr/0010-saq-a-hosted-only-card-capture.md`
- Immutability/retention: `docs/adr/0013-payment-record-immutability-and-retention.md`
- Accounting gate: `docs/adr/0015-fee-refund-chargeback-accounting-deferred.md`
- Naming: `docs/adr/0007-no-third-party-product-names.md`
- Binding: `docs/explanation/payment-compliance.md` §2, §3
- Terms: `docs/terms.md` §6 (gateway identifiers)
