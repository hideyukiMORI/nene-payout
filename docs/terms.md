# Canonical Terms — NeNe Payout

Single source of truth for all identifiers in this repository.
Every identifier in code, API, DB, tests, and docs must match this file exactly.
Typos and unregistered names block merge.

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
| `UseCase` | Business logic (NOT `Service`) |
| `RepositoryInterface` | Repository abstraction |
| `PdoXxxRepository` | PDO implementation |

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
