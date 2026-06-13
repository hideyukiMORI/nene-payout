# Canonical Terms — NeNe Payout

**This file is the single source of truth (唯一の真実) for every identifier and
canonical spelling in this repository. It is binding (ADR 0017).**

This is the **only** file allowed to define the canonical spelling of an
identifier. Every other document (including `glossary.md`) must defer to it and
must never introduce a competing spelling.

## The rule — zero typos, strict enforcement (binding)

1. **Exact match, everywhere.** Every identifier in code, API/JSON, DB,
   tests, OpenAPI, docs, commit scopes, and branch names **MUST** match an entry
   in this file **character-for-character** (case, separators, and spelling).
2. **No unregistered names.** Introducing or renaming any identifier **MUST**
   update this file in the **same PR**. An identifier not registered here is a
   defect.
3. **Typos and 表記ゆれ block merge — no exceptions.** A mismatch (typo, wrong
   case, camelCase vs snake_case, `Nene` vs `NeNe`, etc.) is a merge blocker.
   Reviewers **MUST** reject it. There is no "fix later".
4. **One spelling per concept.** If a concept needs a new name, register it here
   first, then use only that spelling. Never ship two spellings for one thing.
5. **Deletions/renames are tracked.** Renaming an identifier updates this file
   and every usage in the same PR; the old spelling must not remain anywhere.

### How to verify (before every PR)

- Self-check identifiers against this file (`docs/development/self-review.md`).
- Grep for the spelling you are introducing and confirm it matches an entry:
  ```bash
  git grep -n "<identifier>"
  ```
- A CI term-lint check is a planned follow-up; until then, this is a manual,
  reviewer-enforced gate.

## Product & domain names (canonical spelling)

Use these exact spellings — never `Nene`, `nene Payout`, `NenePayout` (in prose),
or other variants in docs/UI.

| Canonical | Use | Wrong |
| --- | --- | --- |
| `NeNe Payout` | Product display name (prose, UI, docs) | `Nene Payout`, `NENE Payout`, `NenePayout` |
| `NeNe Invoice` / `NeNe Clear` / `NeNe Vault` | Sibling product display names | `Nene *`, `nene-*` in prose |
| `NENE2` | Framework display name **in prose** | `Nene2` / `nene2` as prose name |
| `Nene2\` | NENE2 PHP namespace root (code) — correct as-is | writing `NENE2\` in code |
| `nene-payout` | Repository / composer package id | `nene_payout` (that is the DB prefix only) |

## §1 Namespace

| Identifier | Use |
| --- | --- |
| `NenePayout` | PHP namespace root |
| `nene-payout` | Repository name, composer package name |
| `nene_payout` | DB table prefix (where disambiguation is needed) |

## §2 Layer suffixes

| Suffix | Use |
| --- | --- |
| `Handler` | HTTP request handler (NOT `Controller`) |
| `UseCase` / `UseCaseInterface` | Business logic (NOT `Service`); method `execute` |
| `RepositoryInterface` | Repository abstraction |
| `PdoXxxRepository` | PDO implementation |
| `Input` / `Output` | Readonly use-case DTOs |
| `InputMapper` | Request → Input DTO mapper |
| `GatewayAdapter` | Payment gateway adapter (e.g. `StripeGatewayAdapter`) |
| `ServiceProvider` | DI registration per domain concept |
| `Exception` | Named domain exception |
| `Status` | Backed enum for entity status (e.g. `ReceivedInvoiceStatus`) |

## §3 Entities

| Identifier | Type | Notes |
| --- | --- | --- |
| `ReceivedInvoice` | Entity | 受け取った請求書 |
| `Vendor` | Entity | 支払先の仕入先・外注先 |
| `PaymentExecution` | Entity | 決済実行記録 |
| `AuditLog` | Entity | 監査ログ（`audit_logs` テーブル） — ADR 0011 |

## §4 Status values

### ReceivedInvoice.status
| Value | Meaning |
| --- | --- |
| `pending` | 未払い |
| `processing` | 決済中 |
| `paid` | 支払い完了 |
| `failed` | 失敗 |
| `voided` | 無効化（論理削除 — ADR 0013） |

### PaymentExecution.status
| Value | Meaning |
| --- | --- |
| `initiated` | 開始済み |
| `succeeded` | 成功 |
| `failed` | 失敗 |
| `refunded` | 返金（新規リンクレコード — ADR 0013, 0015） |
| `charged_back` | チャージバック（新規リンクレコード — ADR 0013, 0015） |

## §5 Account type values

| Value | Meaning |
| --- | --- |
| `普通` | 普通預金 |
| `当座` | 当座預金 |

## §6 Gateway identifiers

| Value | Notes |
| --- | --- |
| `stripe` | Stripe adapter |
| `gmo_pg` | GMO Payment Gateway adapter |

## §7 JSON field names (snake_case only)

| Field | Notes |
| --- | --- |
| `received_invoice_id` | |
| `vendor_id` | |
| `organization_id` | |
| `amount` | Integer cents — invoice amount the vendor is owed/receives |
| `charge_amount` | Integer cents — amount the operator's card is charged (ADR 0015) |
| `processing_fee` | Integer cents — card-payment processing fee (ADR 0015) |
| `due_date` | ISO 8601 date string (JST calendar date — ADR 0012) |
| `bank_code` | |
| `branch_code` | |
| `account_type` | |
| `account_number` | |
| `account_name` | |
| `registration_number` | Vendor 適格請求書発行事業者 登録番号; format `^T[0-9]{13}$`, syntax-only (ADR 0014) |
| `tax_rate_bps` | Tax rate in basis points; allowed `1000` (10%) / `800` (8% reduced) |
| `taxable_amount` | Integer cents — taxable amount for a tax-rate group (recorded copy) |
| `tax_amount` | Integer cents — consumption tax for a tax-rate group (recorded copy) |
| `vault_document_url` | HTTP reference to a nene-vault document (ADR 0014) |
| `invoice_client_url` | HTTP reference to a nene-invoice client |
| `gateway` | Gateway identifier (§6) |
| `gateway_reference` | Gateway transaction / reference id |
| `initiated_at` | UTC instant (ADR 0012) |
| `completed_at` | UTC instant (ADR 0012) |

## §8 Env variables

| Variable | Notes |
| --- | --- |
| `NENE_PAYOUT_PORT` | HTTP port (default 8900) |
| `NENE_PAYOUT_FRONTEND_PORT` | Vite port (default 5189) |
| `NENE_PAYOUT_MYSQL_PORT` | MySQL port (default 3398) |
| `NENE_PAYOUT_PHPMYADMIN_PORT` | phpMyAdmin port (default 8901) |

## §9 Prohibited spellings

| Wrong | Correct |
| --- | --- |
| `Controller` | `Handler` |
| `Service` | `UseCase` |
| `Repo` | `RepositoryInterface` |
| `orgId` | `organization_id` |
| `vendorId` | `vendor_id` |
| `invoiceId` | `received_invoice_id` |

## §10 Audit action names (ADR 0011)

Format: `{entity}.{verb}` (snake_case entity, past-tense verb).

| Action | Meaning |
| --- | --- |
| `vendor.created` / `vendor.updated` / `vendor.deactivated` | Vendor mutations |
| `received_invoice.created` / `received_invoice.updated` / `received_invoice.voided` | Invoice mutations |
| `payment.initiated` | Payment instruction sent to gateway |
| `payment.succeeded` / `payment.failed` | Gateway result recorded |
| `payment.refunded` / `payment.charged_back` | Post-settlement events (linked records) |
