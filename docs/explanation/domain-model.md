# Domain Model — NeNe Payout

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
| vault_document_url | string\|null | Optional link to nene-vault |
| status | enum | `pending` / `processing` / `paid` / `failed` |
| created_at | datetime | |
| updated_at | datetime | |

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
| amount | int | Integer cents |
| gateway | string | Adapter identifier (e.g. `stripe`) |
| gateway_reference | string\|null | Gateway transaction ID |
| status | enum | `initiated` / `succeeded` / `failed` |
| initiated_at | datetime | |
| completed_at | datetime\|null | |

## Status lifecycle

```
ReceivedInvoice:
  pending → processing → paid
          ↘             ↗ failed
```

## Money rule

All amounts are stored as **integer cents**. ¥1,000 = `100000`. No floats, no DECIMAL in SQLite.
