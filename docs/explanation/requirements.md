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
| Authentication | JWT HS256 via NENE2 BearerAuth |
| Money | Integer cents only — no floats |
| PCI DSS | Card input via gateway iframe only — raw card numbers never stored |
| Data ownership | All invoice and payment records on operator's server |
| Language | ja / en admin UI |
| OpenAPI | All endpoints documented in `docs/openapi/openapi.yaml` |
