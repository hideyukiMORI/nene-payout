# Domain Model — NeNe Payout

Legal / accounting / tax rules governing these entities are binding in
[`payment-compliance.md`](./payment-compliance.md). Where this model and that
document differ, the compliance document wins.

## Core entities

### ReceivedInvoice

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key |
| organization_id | string | Tenant scope |
| vendor_id | FK → Vendor | Payout-owned vendor record |
| amount | int | Integer cents (e.g. 100000 = ¥1,000) |
| due_date | date | Payment deadline |
| pdf_path | string\|null | Local storage path (not exposed in API) |
| vault_document_url | string\|null | Optional link to nene-vault (evidence retention — ADR 0014) |
| registration_number | string\|null | Vendor 登録番号 `^T[0-9]{13}$`, syntax-only (ADR 0014) |
| status | enum | `pending` / `processing` / `paid` / `failed` / `voided` |
| created_at | datetime | UTC instant (ADR 0012) |
| updated_at | datetime | UTC instant |
| voided_at | datetime\|null | Soft delete / void; never hard-deleted (ADR 0013) |

> Per-rate tax breakdown (`tax_rate_bps`, `taxable_amount`, `tax_amount`) is
> recorded **copy-only** for the operator's input-tax-credit hand-off, never
> computed by Payout (ADR 0014). Allowed rates: `1000` (10%) / `800` (8%).

### Vendor

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key |
| organization_id | string | Tenant scope |
| name | string | Vendor display name |
| bank_code | string(4) | 銀行コード |
| branch_code | string(3) | 支店コード |
| account_type | enum | `普通` / `当座` |
| account_number | string(7) | 口座番号 |
| account_name | string | 口座名義（カナ） |
| created_at | datetime | |
| updated_at | datetime | |

### PaymentExecution

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key |
| organization_id | string | Tenant scope |
| received_invoice_id | FK | |
| amount | int | Integer cents — invoice amount the vendor receives |
| charge_amount | int | Integer cents — amount the operator's card is charged (ADR 0015) |
| processing_fee | int | Integer cents — card-payment fee (recorded verbatim; ADR 0015) |
| gateway | string | Adapter identifier (e.g. `stripe`) |
| gateway_reference | string\|null | Gateway transaction ID (opaque; never PAN — ADR 0010) |
| status | enum | `initiated` / `succeeded` / `failed` / `refunded` / `charged_back` |
| initiated_at | datetime | UTC instant (ADR 0012) |
| completed_at | datetime\|null | UTC instant |

> Terminal records are **immutable**; refunds and chargebacks are recorded as
> new linked records, never by mutating the original (ADR 0013). Their accounting
> treatment is deferred pending 税理士/会計士 sign-off (ADR 0015).

### AuditLog

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key |
| actor_user_id | string\|null | Authenticated user (null for system) |
| organization_id | string | Tenant scope |
| action | string | `{entity}.{verb}` — see terms.md §10 |
| entity_type | string | Changed entity type |
| entity_id | string | Changed entity id |
| before_json | json\|null | Sanitized snapshot before (null for create) |
| after_json | json\|null | Sanitized snapshot after (null for delete) |
| created_at | datetime | UTC instant |

Audit snapshots are sanitized: tokens, API keys, and secrets are never written
(ADR 0011).

## Status lifecycle

```
ReceivedInvoice:
  pending → processing → paid
          ↘             ↗ failed
  (any) → voided   (soft delete / void only — never hard-deleted)

PaymentExecution:
  initiated → succeeded → (refunded | charged_back)   (post-settlement = new linked records)
            ↘ failed
```

## Money rule

All amounts (`amount`, `charge_amount`, `processing_fee`, tax figures) are stored
as **integer cents** and kept distinct (ADR 0015). ¥1,000 = `100000`. No floats,
no DECIMAL in SQLite.

## Immutability & retention

Financial records use soft delete / void and are tamper-evident; no hard delete;
no auto-purge before the statutory retention period (in general 7y, up to 10y).
See [`payment-compliance.md`](./payment-compliance.md) §7 and ADR 0013.
