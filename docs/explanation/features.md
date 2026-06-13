# Features — NeNe Payout

## Phase 1 — Core payment API

| Feature | Description |
| --- | --- |
| Health endpoint | `GET /health` |
| JWT authentication | Bearer token, organization-scoped |
| Vendor CRUD | Create / read / update / deactivate vendors |
| Received invoice CRUD | Register, list, detail, PDF upload |
| Payment gateway adapter | `PaymentGatewayInterface` with Stripe implementation |
| Payment initiation | POST to create PaymentExecution, returns gateway charge URL / token |
| Webhook handler | Receive gateway result, update statuses |
| Gateway config admin | Store and verify gateway credentials per org |
| OpenAPI validation | `composer openapi` passes |

## Phase 2 — Admin UI + widget

| Feature | Description |
| --- | --- |
| React admin UI | Received invoice list, vendor list, payment history |
| Embeddable widget | `<script>` tag, `data-*` parameters, modal / inline mode |
| CSS customization | CSS variables for color, font, border |
| Admin gateway panel | Configure adapter, run 疎通確認 |
| ja / en UI | Runtime locale switch |

## Phase 3 — Tier A deployment

| Feature | Description |
| --- | --- |
| Web installer | Browser-based setup wizard for shared hosting |
| Release ZIP | Download and extract to deploy |
| Operator guide | Docs for non-technical operators |

## Phase 4 — Extended gateways + integrations

| Feature | Description |
| --- | --- |
| GMO Payment Gateway adapter | Second gateway implementation |
| nene-suite integration | Suite orchestrator can launch Payout |
| nene-vault document link | Link received invoice to vault document |
| nene-invoice vendor cross-reference | Reference nene-invoice client by ID |
