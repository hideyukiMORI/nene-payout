# Naming Conventions — NeNe Payout

All identifiers must match [`docs/terms.md`](../terms.md) exactly. This file explains the rules behind the registry.

## PHP

| Pattern | Convention | Example |
| --- | --- | --- |
| Namespace root | `NenePayout\` | `NenePayout\ReceivedInvoice\Handler\ListHandler` |
| HTTP handler | `XxxHandler` (never `Controller`) | `CreateReceivedInvoiceHandler` |
| UseCase logic | `XxxUseCase` + `XxxUseCaseInterface` (never `Service`) | `InitiatePaymentUseCase` |
| Repository abstraction | `XxxRepositoryInterface` | `ReceivedInvoiceRepositoryInterface` |
| PDO implementation | `PdoXxxRepository` | `PdoReceivedInvoiceRepository` |
| Gateway adapter | `XxxGatewayAdapter` | `StripeGatewayAdapter` |
| Gateway interface | `PaymentGatewayInterface` | — |
| Input DTO | `XxxInput` (final readonly) | `CreateVendorInput` |
| Output DTO | `XxxOutput` (final readonly) | `InitiatePaymentOutput` |
| Input mapper | `XxxInputMapper` | `CreateVendorInputMapper` |
| Service provider | `XxxServiceProvider` | `PaymentServiceProvider` |
| Entity / value object | `Xxx` (final readonly), status `XxxStatus` (backed enum) | `Vendor`, `ReceivedInvoiceStatus` |
| Exception | `XxxException` | `PaymentFailedException` |

The single UseCase method is always named `execute`. DTOs are `final readonly`,
constructed from already-validated values. Group files by **domain concept**
(`src/{Domain}/...`), not by layer type — see
[`backend-standards.md`](./backend-standards.md).

## Database

| Convention | Notes |
| --- | --- |
| Table names | `snake_case`, plural | `received_invoices`, `vendors`, `payment_executions` |
| Column names | `snake_case` | `organization_id`, `bank_code`, `account_name` |
| Tenant scope | Every tenant table has `organization_id` | Required on all queries |
| Primary key | `id` (ULID string) | |
| Timestamps | `created_at`, `updated_at` | |

## JSON API

| Convention | Notes |
| --- | --- |
| Field names | `snake_case` — never camelCase | `received_invoice_id`, `due_date` |
| Amounts | Integer cents | `"amount": 100000` |
| Dates | ISO 8601 | `"due_date": "2026-07-31"` |
| Datetimes | ISO 8601 UTC | `"initiated_at": "2026-07-01T10:00:00Z"` |

## Prohibited patterns

| Wrong | Correct |
| --- | --- |
| `Controller` suffix | `Handler` |
| `Service` suffix | `UseCase` |
| `Repo` abbreviation | `RepositoryInterface` / `PdoXxxRepository` |
| `orgId` | `organization_id` |
| camelCase JSON fields | snake_case |
| Float for money | Integer cents |
