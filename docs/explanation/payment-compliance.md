# Payment, Legal & Tax Compliance — Binding Rules

**Status: binding (non-negotiable).** This document is the source of truth for
NeNe Payout's adherence to Japanese payment-services, consumption-tax, and
record-retention law on the **payer side** (a business paying received vendor
invoices by card). A finance, accounting, or legal professional reviewing the
system must be able to find **zero deviations** from the rules below.

These are not guidelines. They are **MUST** requirements. Where a rule here
conflicts with UX, performance, implementation convenience, or any other
concern, **compliance wins** — every time, without exception.

> **Not legal advice.** This document is engineering's binding *interpretation*
> of the applicable rules so that the software does not, by construction, put its
> operator or NeNe Payout in a non-compliant position. The legal authority is a
> 税理士 / 会計士 / 弁護士, not this repository. Where a requirement is unclear,
> **stop and consult a professional** — do not guess.

See also: [`scope-contract.md`](./scope-contract.md),
[`domain-model.md`](./domain-model.md),
[`requirements.md`](./requirements.md),
self-review checklist [`../review/compliance.md`](../review/compliance.md),
and ADRs 0008–0015.

---

## 0. Governing principle

1. **Compliance is non-negotiable.** Correct adherence to the law takes
   precedence over every other product goal.
2. **No silent deviation.** Any departure from the rules in this document — even
   temporary — requires an **ADR** and **explicit review sign-off by a tax /
   accounting / legal professional (税理士・会計士・弁護士)** recorded in that
   ADR. Code may not merge a deviation without it.
3. **Engineering is not the legal authority.** This document is engineering's
   binding interpretation of the rules. When unclear, **stop and consult a
   professional** — do not guess. Record the resolved interpretation here.
4. **Single source of truth for figures.** Every monetary, fee, and tax figure
   is computed once in the UseCase layer. The API response, the stored record,
   and any UI render the exact same values; no layer recalculates independently.
5. **Software, not a financial institution.** NeNe Payout is self-hosted software
   that *instructs* and *records* payments. It does not itself move money, hold
   funds, or perform any licensed financial activity (§2).

---

## 1. Statutory awareness

NeNe Payout is built to operate without breaching the following Japanese rules.
This list states *what we are careful about*; it is not legal advice and is not
exhaustive.

| Area | Rule set | How Payout positions itself |
| --- | --- | --- |
| Money movement / remittance | 資金決済法（為替取引・資金移動業）, 銀行法 | **Delegated in full** to the licensed payment gateway — ADR 0009 |
| Collection on behalf | 収納代行 | Performed by the gateway, not Payout — ADR 0009 |
| Card installment / credit | 割賦販売法 | Cardholder ↔ card issuer relationship; outside Payout — §2 |
| AML / CFT, identity verification | 犯罪収益移転防止法（取引時確認） | Gateway + operator responsibility — ADR 0009 |
| Card data security | PCI DSS | Hosted-only capture, SAQ-A, no PAN — ADR 0010 |
| Consumption tax / qualified invoice | 消費税法, 適格請求書等保存方式（インボイス制度） | **Record & link only**, never the deduction authority — ADR 0014, §5 |
| Electronic record retention | 電子帳簿保存法（電子取引データの保存） | Payment records are tamper-evident, no auto-purge — ADR 0013, §7 |
| Subcontractor / freelancer payment terms | 下請法, フリーランス・事業者間取引適正化等法 | Records `due_date`; does **not** assert legal compliance — §8 |

When any of these change, treat it as a compliance defect until the product is
re-reviewed, and open a P0 Issue.

---

## 2. Legal positioning — Payout is not a money transmitter (binding)

The actual movement of money — charging the operator's card and transferring
funds to the vendor's bank account — is performed **entirely by a licensed
payment gateway**. NeNe Payout's role is strictly limited to:

- recording a received invoice and the vendor's bank account details,
- presenting the operator with a gateway-hosted payment flow,
- sending a charge *instruction* to the gateway via `PaymentGatewayInterface`,
- recording the gateway's result (success / failure / refund / chargeback).

Therefore, **binding**:

- Payout **MUST NOT** hold, pool, escrow, or take custody of operator or vendor
  funds at any time.
- Payout **MUST NOT** itself execute 為替取引 (exchange transactions), act as a
  資金移動業者 (funds-transfer service), or perform 収納代行 (collection on
  behalf). These are the gateway's regulated functions.
- Payout **MUST NOT** perform 取引時確認 (AML/KYC) itself, nor present itself as
  satisfying the operator's AML obligations. It records the vendor account the
  operator entered; identity verification is the gateway's and operator's duty.
- Any future feature that would move money through Payout's own
  server/account, or that would place Payout in a licensed-activity position,
  **requires a new ADR with legal sign-off** before any code.

Docs, UI copy, and marketing **MUST NOT** describe Payout as a payment service,
remittance service, or financial institution. It is software that connects the
operator to a licensed gateway.

---

## 3. Card data security (PCI DSS) — binding

- The card PAN **MUST NOT** pass through the application, its database, or the
  operator's server. Only **gateway-hosted redirect or processor-hosted iframe**
  capture is permitted (tokenization). See ADR 0010.
- Payout stores only opaque references (gateway session id, payment intent / token,
  `gateway_reference`) and webhook event payloads — **never** card numbers,
  **never** CVV, **never** expiry as cardholder data.
- A self-host operator who enables card payment **MUST** remain at **PCI DSS
  SAQ-A**. Any gateway adapter that would raise that scope requires a new ADR.
- Card tokens, gateway API keys, and webhook secrets **MUST NOT** be logged.
  Sensitive tokens are hashed (SHA-256) before any storage that needs them.

---

## 4. Amount integrity — three amounts kept distinct (binding)

A card payment of a vendor invoice involves up to three distinct figures. They
**MUST** be modelled and stored separately, never conflated:

| Figure | Meaning | Identifier (terms.md §10) |
| --- | --- | --- |
| Invoice amount | What the vendor is owed / receives | `amount` |
| Charge amount | What the operator's card is charged | `charge_amount` |
| Processing fee | Fee charged for the card-payment service | `processing_fee` |

- The relationship between these amounts (fee added on top vs. netted) is
  **gateway- and contract-dependent** and is **compliance-bound**: see §10.
- The system **MUST NOT** silently assume `charge_amount == amount`. The stored
  record always carries the gateway-reported figures.
- All three are a **single source of truth** computed/recorded once in the
  UseCase; API and UI render them unchanged.

---

## 5. Consumption tax & input-tax-credit evidence — record & link only (binding)

NeNe Payout is the **payer**. For the operator to claim 仕入税額控除 (input tax
credit), the operator must retain the vendor's **qualified invoice (適格請求書)**
and a record of payment. Payout's role is **record and link only** (ADR 0014):

- Payout **MAY** record, against a received invoice, the vendor's
  **registration number** (`registration_number`, format `^T[0-9]{13}$`) and a
  per-rate tax breakdown when the operator provides it.
- Registration number validation is **syntax only**. It does **not** prove the
  number exists, is registered, or is valid, and the system does **not** perform
  check-digit or registry lookup. UI and docs **MUST NOT** present a format pass
  as proof of validity.
- Allowed consumption-tax rates for any recorded breakdown are **10% (1000 bps)**
  and **8% reduced (800 bps)**. Recording any other rate is a compliance change
  requiring an ADR.
- Payout **MUST NOT** present itself as the legal retention store for the
  qualified invoice, nor as the authority that determines deductibility. The
  qualified invoice itself is retained via **nene-vault** / the operator's
  電子帳簿保存法 retention; Payout stores a reference (`vault_document_url`).
- Payout **MUST NOT** compute or assert the operator's deductible tax amount.
  Figures recorded are descriptive copies of what the vendor invoice states,
  for matching and hand-off — not a tax calculation engine.

---

## 6. Money representation (binding)

- All monetary amounts (`amount`, `charge_amount`, `processing_fee`, any tax
  figure) are stored and transmitted as **integer minimum currency units**
  (for JPY, ¥1 = 1 unit). **Float and DECIMAL for money are prohibited** in DB,
  API JSON, and tests.
- Phase 1 currency is **JPY only**. Multi-currency is a future ADR.

---

## 7. Immutability, void, and retention (binding)

- **Payment execution records are immutable.** Once a `PaymentExecution` reaches
  a terminal state (`succeeded` / `failed`), its amounts, gateway reference,
  timestamps, and status history **MUST NOT** be edited or deleted. Later events
  (refund, chargeback) are recorded as **new linked records / status
  transitions**, never by mutating the original (ADR 0013).
- **No hard delete of financial records.** Received invoices, payment
  executions, and vendor account snapshots used in a payment use **soft delete /
  void** semantics. A voided record is recorded as voided, not erased.
- **Tamper-evident.** A stored payment record **MUST NOT** be silently mutated;
  corrections produce a new versioned/linked record.
- **Retention.** Payment records and electronically received invoice data are
  **電子取引データ** under 電子帳簿保存法 and corporate-tax bookkeeping rules:
  retain for the **statutory period (in general 7 years, up to 10 years** in
  loss-carryforward situations). The product **MUST NOT** auto-purge financial
  records before the statutory period. Operators are warned before any
  destructive retention action.

---

## 8. Subcontractor / freelancer payment terms — record, do not assert (binding)

If an operator pays subcontractors or freelancers, statutory payment-deadline
rules (下請法 / フリーランス・事業者間取引適正化等法) may apply.

- Payout **records** `due_date` and payment timestamps so the operator has the
  data, but **MUST NOT** claim to enforce or certify compliance with these
  deadline rules.
- UI and docs **MUST NOT** state or imply that using Payout makes the operator
  compliant with 下請法 / フリーランス新法. Responsibility stays with the operator.

---

## 9. Audit trail (binding)

- Every mutating operation — vendor create/update/deactivate, received invoice
  create/update/void, payment initiation, and each payment status transition
  (including webhook-driven ones) — is recorded as an auditable event:
  **who / when / what / before / after** (ADR 0011).
- Audit snapshots are **sanitized**: card tokens, gateway API keys, webhook
  secrets, and any PAN-adjacent data are **never** written to the audit trail.
- Audit records follow the same no-silent-mutation rule as §7.

---

## 10. Fee, refund, and chargeback accounting — gated on professional sign-off (binding)

How the processing fee, refunds, and chargebacks map onto the recorded figures
and the operator's books carries **more compliance risk than the payment flow
itself**, and its treatment can differ by gateway contract.

- The fee/refund/chargeback **accounting model MUST NOT be implemented** until it
  is reviewed and signed off by a **税理士 / 会計士**, recorded in an ADR
  (ADR 0015 fixes the *constraint*; it does not yet fix the ledger design).
- Until then, Payout records the **gateway-reported amounts verbatim**
  (`amount`, `charge_amount`, `processing_fee`, refund/chargeback events) without
  asserting their tax character (e.g. whether a fee is 課税 / 非課税) or their
  bookkeeping classification.
- Consumption-tax character of the processing fee, net-of-fee vs. gross
  modelling, refund reversal, and chargeback handling are all **out of scope
  until the signed-off ADR exists**.

---

## 11. How this rule applies to every change

Any change that touches received invoices, vendors, payment execution, amounts,
fees, tax fields, gateway integration, webhook handling, document references, or
retention **MUST**:

1. Be reviewed against this document and [`../review/compliance.md`](../review/compliance.md).
2. State compliance impact in the PR.
3. If it deviates from any rule here, carry an ADR with professional sign-off
   (§0.2). No exceptions.

If you are unsure whether a change has compliance impact, **assume it does** and
run the checklist.
