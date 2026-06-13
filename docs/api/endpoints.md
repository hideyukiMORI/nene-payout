# API Endpoint Inventory — NeNe Payout

Human-readable map of the planned API surface. The machine contract (source of
truth) is [`../openapi/openapi.yaml`](../openapi/openapi.yaml). Implementation is
Phase 1; endpoints are added per `docs/development/endpoint-scaffold` discipline
(route + OpenAPI + tests together).

## Conventions

- **Base path:** `/api/v1/...` (collections are plural kebab-case). `/health` is unversioned.
- **Auth:** `Authorization: Bearer <JWT>` (NENE2 `BearerTokenMiddleware`). Exceptions:
  `/health`, `/auth/login`, `/webhooks/*` (signature-verified), `/widget/*` (`X-Widget-Token`).
- **Tenant:** resolved from the request (`OrgResolverMiddleware`), never from body
  (ADR 0018). Superadmin org-management and auth/health/webhook routes bypass resolution.
- **Pagination:** `?limit=&offset=`; envelope `{ items, limit, offset, total? }`.
- **Errors:** RFC 9457 Problem Details (`application/problem+json`); validation = 422 with `errors[]`.
- **Money:** integer minimum currency units; `amount` / `charge_amount` / `processing_fee` distinct.
- **IDs:** ULID strings. **Dates:** `due_date` JST; instants UTC.

## Endpoints

| Method & path | operationId | Auth / scope | Capability | Notes |
| --- | --- | --- | --- | --- |
| `GET /health` | health | none | — | Liveness |
| `POST /api/v1/auth/login` | login | none | — | Issues JWT |
| `GET /api/v1/auth/me` | getCurrentUser | bearer | any | Current user |
| `GET /api/v1/organizations` | listOrganizations | bearer / cross-tenant | ManageOrganizations | superadmin |
| `POST /api/v1/organizations` | createOrganization | bearer / cross-tenant | ManageOrganizations | superadmin |
| `GET /api/v1/organizations/{id}` | getOrganization | bearer / cross-tenant | ManageOrganizations | superadmin |
| `PATCH /api/v1/organizations/{id}` | updateOrganization | bearer / cross-tenant | ManageOrganizations | superadmin |
| `POST /api/v1/organizations/{id}/deactivate` | deactivateOrganization | bearer / cross-tenant | ManageOrganizations | soft |
| `GET /api/v1/vendors` | listVendors | bearer / org | ManageVendors (read: admin/operator) | filter `q` |
| `POST /api/v1/vendors` | createVendor | bearer / org | ManageVendors | admin |
| `GET /api/v1/vendors/{id}` | getVendor | bearer / org | — | |
| `PATCH /api/v1/vendors/{id}` | updateVendor | bearer / org | ManageVendors | admin |
| `POST /api/v1/vendors/{id}/deactivate` | deactivateVendor | bearer / org | ManageVendors | soft |
| `GET /api/v1/received-invoices` | listReceivedInvoices | bearer / org | RegisterInvoice/View | filter status/vendor/due range |
| `POST /api/v1/received-invoices` | createReceivedInvoice | bearer / org | RegisterInvoice | admin/operator |
| `GET /api/v1/received-invoices/{id}` | getReceivedInvoice | bearer / org | — | + payment history |
| `PATCH /api/v1/received-invoices/{id}` | updateReceivedInvoice | bearer / org | RegisterInvoice | only while `pending` (409 otherwise) |
| `POST /api/v1/received-invoices/{id}/void` | voidReceivedInvoice | bearer / org | RegisterInvoice | soft |
| `POST /api/v1/received-invoices/{id}/pdf` | uploadReceivedInvoicePdf | bearer / org | RegisterInvoice | multipart; not served via API |
| `POST /api/v1/received-invoices/{id}/payments` | initiatePayment | bearer / org | InitiatePayment | returns gateway target; no PAN |
| `GET /api/v1/payment-executions` | listPaymentExecutions | bearer / org | ViewPayments | filter status/invoice |
| `GET /api/v1/payment-executions/{id}` | getPaymentExecution | bearer / org | ViewPayments | |
| `GET /api/v1/gateway-settings` | getGatewaySettings | bearer / org | ManageGatewaySettings | secrets masked |
| `PUT /api/v1/gateway-settings` | updateGatewaySettings | bearer / org | ManageGatewaySettings | admin |
| `POST /api/v1/gateway-settings/verify` | verifyGatewayConnectivity | bearer / org | ManageGatewaySettings | 疎通確認 |
| `GET /api/v1/users` | listUsers | bearer / org | ManageOrganizationSettings | admin |
| `POST /api/v1/users` | createUser | bearer / org | ManageOrganizationSettings | admin |
| `GET /api/v1/users/{id}` | getUser | bearer / org | ManageOrganizationSettings | admin |
| `PATCH /api/v1/users/{id}` | updateUser | bearer / org | ManageOrganizationSettings | admin |
| `POST /api/v1/users/{id}/deactivate` | deactivateUser | bearer / org | ManageOrganizationSettings | soft |
| `GET /api/v1/audit-logs` | listAuditLogs | bearer / org (superadmin cross) | — (admin) | filter entity/actor/action/date |
| `POST /api/v1/webhooks/{gateway}` | receiveGatewayWebhook | signature | — | idempotent; updates payment + invoice |
| `GET /api/v1/widget/context` | getWidgetContext | widget token | — | invoice summary + locale |
| `POST /api/v1/widget/payments` | initiateWidgetPayment | widget token | — | initiate from widget |

## Out of scope (by design)

- Invoice issuance, deposit reconciliation, document archiving (sibling products — ADR 0002).
- Fee/refund/chargeback **accounting** endpoints until a 税理士-signed ADR exists (ADR 0015).
- Any endpoint that would move money through Payout or accept card PAN (ADR 0009, 0010).

## Related

- Contract: [`../openapi/openapi.yaml`](../openapi/openapi.yaml)
- Domain model: [`../explanation/domain-model.md`](../explanation/domain-model.md)
- Multi-tenancy: [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md)
- Roles/capabilities: [`../terms.md`](../terms.md) §11 / [`../explanation/pages.md`](../explanation/pages.md)
