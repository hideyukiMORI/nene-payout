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
   product used for **capture** is confirmed and is SAQ-A eligible.
   → **Material gathered (2026-08-14), see §Material 2 below. Checkout is SAQ A.**
2'. **Contracted product for the payout leg.** Split out of item 2 on 2026-08-14:
   item 2 as written only covers the money coming *in* (card capture). Payout's
   product definition also has the gateway send funds *out*, to the vendor's bank
   account. The two legs are different Stripe products with different constraints,
   and the outbound one is **not yet determined**. → §Material 2'.
3. **KYC boundary:** the operator's 取引時確認 (KYC) ownership is documented; Payout
   performs none of it. → **Material gathered (2026-08-14), see §Material 3.**
   The material shows this item cannot be answered independently of item 2'.

## Material for open items 2, 2' and 3

> **Status of this section: material, not decision.** Everything below was read
> from Stripe's public documentation on 2026-08-14 and is quoted or cited so the
> signer can verify it without re-deriving it. **This section deliberately draws no
> legal conclusion** — under ADR 0009 the sign-off is the operator's
> (代表取締役) own judgement, and a product document that pre-writes the conclusion
> would be embedding an assurance nobody actually checked. Where something is not
> established, it says so.

### §Material 2 — capture: Checkout is SAQ A

Stripe's PCI compliance guide states the SAQ type per integration method:

| Integration | SAQ | Stripe's wording (JP guide) |
| --- | --- | --- |
| Checkout / Stripe.js + Elements | **A** | 「Checkout と Stripe.js and Elements は、すべてのカードデータ収集入力を (お客様のドメインではなく) Stripe のドメインが提供する iframe 内でホストします。」 |
| Stripe.js v2 posting a form hosted on your own site | A-EP | 「自社サイトでホストされているフォームに入力されたカードデータを Stripe.js v2 で送信する場合、毎年 SAQ A-EP を完了させ…」 |
| Card data passed to the API directly | D | 「…SAQ の中で最も要件が厳しい SAQ D を使用して、毎年 PCI 準拠を証明する必要があります。」 |

This matches the Decision above: a Stripe-hosted capture flow keeps the PAN off
Payout's server and database, which is what ADR 0010 requires. **Item 2 is
answerable on public documentation alone** — what remains is contractual
confirmation of which product the operator actually contracts for.

### §Material 2' — the payout leg is not covered by the capture product

Checkout concerns money coming **in**. Payout's product definition
(`README.md`, `docs/explanation/scope-contract.md`) has funds reaching the
**vendor's** bank account. Two measured facts bear on this:

1. **Stripe's third-party payout product is not available in Japan.**
   Global Payouts lists its availability as **`GB` and `US` only**
   〔measured 2026-08-14, "利用可否" section of the Global Payouts documentation〕.
   ⚠️ **Do not confuse this with the "160 か国以上へ送金できる" figure on the same page.**
   That number is the set of **destination** countries money can be sent *to*;
   「利用可否」is the set of countries the **sending business** may operate from.
   They are different axes, and reading the first as covering the second would give
   "160 countries, so Japan is fine" — which the page does not say.
2. **Stripe's own documentation routes the licensed / segregated-funds case to a
   different product.** The same page says Global Payouts also suits businesses
   「**資金移動を仲介するために必要なライセンス (資金移動業ライセンスなど) を保有している**」, and:
   > 「ユーザーに代わって分別された資金を保有する必要がある場合、またはライセンスが必要な場合は、
   > **Stripe Connect をご検討ください。**」

**Therefore item 2 as originally written cannot close the ADR**: naming Checkout
answers only the inbound half. Which product carries the outbound half — and
whether that product is available to a Japan-based operator — is **undetermined**,
and it is the half that touches item 1's licensing question most directly.
**Payout does not propose an answer here**; establishing it needs the operator's
contractual and legal position, not more public-documentation reading.

### §Material 3 — KYC: true of the software, and not the end of the question

Payout performing no 取引時確認 is a statement about **this codebase**, and it holds:
there is no identity-verification code, no identity document handling, and no
identity data in the schema. The material below is about where the obligation
then sits, which is what open item 3 asks to document.

From Stripe's Connect identity-verification documentation:

- 「**Connect プラットフォームは、ユーザーから必要な情報を収集し、Stripe に提供します。**
  …その後、Stripe 検証を試みます。」
- 「**プラットフォームが個別の法定本人確認や確認要件を満たす際に Stripe の確認に依存することはできません。**」
- 「Stripe が連結アカウントを確認した後も、プラットフォームは引き続き**不正使用を監視し防止する**必要があります。」

From Stripe's Connect service-agreement-types documentation:

| Agreement | Relationship | Capability |
| --- | --- | --- |
| `full` | 「**Stripe と連結アカウントの所有者の間**のサービス関係を構築します」 | can process card payments |
| `recipient` | 「**Stripe が受取人との直接的なサービス関係を持たない**ことを認めます。代わりに、**受取人はプラットフォームとの関係のみを持ちます**」 | cannot process payments; no `card_payments` |

Two consequences worth putting in front of the signer:

- **The answer to item 3 depends on the answer to item 2'.** Under a `recipient`
  agreement the vendor has a relationship with the **platform**, not with Stripe —
  so "KYC ownership stays with Stripe and the operator" resolves differently than
  under `full`. The Decision's current sentence is not wrong, but it is not yet
  specific enough to sign.
- **"Stripe verifies, so we do not have to" is explicitly not available**:
  Stripe's own documentation says a platform cannot rely on Stripe's verification
  to satisfy its own statutory identity-verification requirements. Whether the
  operator has such a requirement is a legal question, deliberately left open here.

### Sources (retrieved 2026-08-14)

- PCI compliance guide — https://stripe.com/guides/pci-compliance
- Integration security guide — https://docs.stripe.com/security/guide
- Connect: identity verification — https://docs.stripe.com/connect/identity-verification
- Connect: service agreement types — https://docs.stripe.com/connect/service-agreement-types
- Global Payouts — https://docs.stripe.com/global-payouts

Public documentation changes without notice; **re-read these before signing**
rather than trusting this transcription. (Measured the same week: an advisory
range this repository depended on was rewritten upstream three days after a
correctly-written exception cited it — see #295.)

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
