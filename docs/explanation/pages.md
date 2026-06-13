# Pages — NeNe Payout

Admin UI pages (React). All paths are under the admin shell.

## Public

| Path | Description | Auth |
| --- | --- | --- |
| `/widget` | Embeddable payment form (standalone, loaded by widget.js) | None (token-gated) |
| `/widget/complete` | Payment result screen | None |

## Admin UI

| Path | Description | Roles |
| --- | --- | --- |
| `/admin/login` | Login | — |
| `/admin/dashboard` | Summary: pending invoices, recent payments | admin, operator |
| `/admin/received-invoices` | Received invoice list (filter by status / vendor / due date) | admin, operator |
| `/admin/received-invoices/new` | Register new received invoice | admin, operator |
| `/admin/received-invoices/:id` | Invoice detail + payment history | admin, operator |
| `/admin/received-invoices/:id/pay` | Initiate payment (card form) | admin, operator |
| `/admin/vendors` | Vendor list | admin |
| `/admin/vendors/new` | Register new vendor | admin |
| `/admin/vendors/:id` | Vendor detail / edit | admin |
| `/admin/settings/gateway` | Payment gateway configuration + 疎通確認 | admin |
| `/admin/settings/organization` | Organization settings | admin |
| `/admin/users` | User management | admin |
| `/admin/audit-logs` | Audit trail (who changed what, before/after; filter + CSV) | admin (own org), superadmin (cross-tenant) |

## Role definitions

| Role | Scope |
| --- | --- |
| `superadmin` | Cross-tenant (Suite orchestration only) |
| `admin` | Full access within own organization |
| `operator` | Create invoices and initiate payments; no settings |
