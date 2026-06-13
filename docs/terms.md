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

## §4 Status values

### ReceivedInvoice.status
| Value | Meaning |
| --- | --- |
| `pending` | 未払い |
| `processing` | 決済中 |
| `paid` | 支払い完了 |
| `failed` | 失敗 |

### PaymentExecution.status
| Value | Meaning |
| --- | --- |
| `initiated` | 開始済み |
| `succeeded` | 成功 |
| `failed` | 失敗 |

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
| `amount` | Integer cents |
| `due_date` | ISO 8601 date string |
| `bank_code` | |
| `branch_code` | |
| `account_type` | |
| `account_number` | |
| `account_name` | |
| `gateway_reference` | |
| `initiated_at` | |
| `completed_at` | |

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
