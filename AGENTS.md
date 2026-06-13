# Agent / AI Guide

Entry point for AI agents working on **NeNe Payout** (public repo `nene-payout`).

## Domain (read first)

| Product | Repository | Domain |
| --- | --- | --- |
| **NeNe Invoice** | `nene-invoice` | Quote, invoice, payment management |
| **NeNe Clear** | `nene-clear` | Payment reconciliation & dunning |
| **NeNe Vault** | `nene-vault` | Received-document archive |
| **NeNe Payout** | `nene-payout` (this) | Vendor invoice payment via credit card |

See [ADR 0002](docs/adr/0002-separate-from-sibling-products.md).

## Read First

- **Canonical terms — single source of truth (binding):** `docs/terms.md` ← **START HERE for any identifier**
- **Scope contract (binding):** `docs/explanation/scope-contract.md`
- **Payment / legal / tax compliance (binding, non-negotiable):** `docs/explanation/payment-compliance.md`
- **Compliance self-review (binding):** `docs/review/compliance.md`
- **Product vision:** `docs/explanation/product-vision.md`
- **Requirements:** `docs/explanation/requirements.md`
- **Domain model:** `docs/explanation/domain-model.md`
- **Naming rules:** `docs/development/naming-conventions.md`
- **Backend standards:** `docs/development/backend-standards.md`
- **NENE2 compliance (binding):** `docs/development/nene2-compliance.md`
- **NENE2 runtime reference — common objects, data flow (binding):** `docs/development/nene2-runtime-reference.md`
- **Database & schema (binding):** `docs/development/database-standards.md`
- **Frontend standards (binding):** `docs/development/frontend-standards.md`
- **Sibling integration:** `docs/integrations/sibling-products.md`
- **NENE2 inheritance map:** `docs/inheritance-from-nene2.md`
- **Current work:** `docs/todo/current.md`
- **Roadmap:** `docs/roadmap.md`

## Operating Rules

- Issue-driven; no direct commits to `main`
- Do **not** add invoice issuance — **`nene-invoice`**
- Do **not** add bank reconciliation / dunning — **`nene-clear`**
- Do **not** add long-term document archiving — **`nene-vault`**
- Do **not** add full accounts payable management
- Do **not** move money, hold funds, or do AML/KYC in Payout — **delegated to the gateway** (ADR 0009)
- Do **not** store card PAN — hosted-only capture, SAQ-A (ADR 0010)
- Do **not** implement fee/refund/chargeback accounting without a 税理士/会計士-signed ADR (ADR 0015)
- **Compliance is binding & non-negotiable** — `docs/explanation/payment-compliance.md`; run `docs/review/compliance.md`
- **Follow NENE2 conventions** — `docs/development/nene2-compliance.md`
- Namespace: `NenePayout\`; money: integer cents
- **Repository docs: English only** (ADR 0006)

## Framework

[NENE2](https://github.com/hideyukiMORI/NENE2) via Composer (`vendor/hideyukimori/nene2/`).
