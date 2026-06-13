# Requirements — NeNe Payout

## Functional requirements

### Vendor management

- Register vendor with name and bank account details (bank code, branch code, account type, account number, account name)
- Edit and deactivate vendors
- List vendors with pagination

### Received invoice management

- Register a received invoice: amount, due date, vendor, optional PDF upload
- Link to a nene-vault document by URL (optional)
- List and search received invoices (by status, vendor, due date range)
- View invoice detail with payment history

### Payment execution

- Select a registered invoice and initiate card payment
- Card input via secure gateway iframe (no raw card numbers on own server)
- Confirm payment details before submission
- Record PaymentExecution result (succeeded / failed)
- Update ReceivedInvoice status automatically on result

### Webhook

- Receive payment result webhook from gateway
- Validate webhook signature
- Update PaymentExecution and ReceivedInvoice status

### Admin: gateway configuration

- Register gateway credentials (API key, etc.) per organization
- Run connectivity verification (疎通確認) from admin panel
- Switch active gateway adapter

### Embeddable widget

- Single `<script>` tag embed with `data-*` attributes
- CSS variable customization for color / font / border
- Opens payment form in modal or inline

## Non-functional requirements

| Requirement | Target |
| --- | --- |
| Tier A (shared hosting) | PHP 8.4, SQLite, mod_php or PHP-FPM |
| Tier B (Docker) | PHP 8.4, MySQL 8, Docker Compose |
| Authentication | JWT HS256 via NENE2 `BearerTokenMiddleware` + `LocalBearerTokenVerifier` (`NENE2_LOCAL_JWT_SECRET`) |
| Multi-tenancy | Tenant resolved from request (`OrgResolverMiddleware`) → `RequestScopedHolder`; repos filter `organization_id` (ADR 0004, 0018) |
| Money | Integer cents only — no floats |
| PCI DSS | Hosted-only capture, SAQ-A — raw card numbers (PAN) never stored (ADR 0010) |
| Legal positioning | Software only; all regulated money movement delegated to the licensed gateway — not a 資金移動業/為替 (ADR 0009) |
| Record integrity | Financial records immutable, tamper-evident, soft delete / void only (ADR 0013) |
| Retention | No auto-purge before the statutory period (in general 7y, up to 10y) — 電子帳簿保存法 (ADR 0013) |
| Audit trail | Every mutating op + payment status transition, sanitized before/after (ADR 0011) |
| Time | UTC storage, JST display/derivation for statutory dates (ADR 0012) |
| Tax evidence | Record & link only (registration_number, per-rate breakdown); not the deduction authority (ADR 0014) |
| Money | Integer cents only — no floats; `amount` / `charge_amount` / `processing_fee` kept distinct |
| Data ownership | All invoice and payment records on operator's server |
| Language | ja / en, instant runtime switch; all UI text in message catalogs (i18n.md) |
| OpenAPI | All endpoints documented in `docs/openapi/openapi.yaml` |
| Compliance gate | Binding `docs/explanation/payment-compliance.md`; deviations need a 税理士/会計士-signed ADR (ADR 0008) |
