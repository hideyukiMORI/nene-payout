# ADR 0021 — Embeddable Widget: Two Invocation Modes (Programmatic Pay + Embedded Invoice Management)

Date: 2026-06-18
Status: Proposed

## Context

The embeddable widget is the product's primary integration surface (scope-contract
"DO": *Provide an embeddable widget for integration into existing systems*).
The target operator already runs an in-house system but has **no way to pay a
received invoice by card** — today they must leave their system for an external
service (financial data leaves their environment), and they cannot fund building
card payment into their own system. The widget must therefore drop into the
operator's own system via a single `<script>` tag with **zero integration
development**, feel like a native part of that system, and keep all data on the
operator's own server (self-host).

Two real host situations exist, and the product must serve **both**:

- **Pattern A — the host already manages invoices.** The operator's screen has the
  invoice; a button there calls the widget, passing the invoice number, payee
  (振込先 = vendor bank account), amount and due date as arguments. The widget
  performs the card payment for that invoice.
- **Pattern B — the host does not manage invoices.** The widget itself renders a
  small invoice-management interface (list / register / edit / void, vendor/振込先
  management, and invoice image/PDF upload), and the operator pays from there.

The currently documented contract is **narrower than this need**: `docs/openapi/openapi.yaml`
specifies only `GET /api/v1/widget/context` + `POST /api/v1/widget/payments` with a
token "carrying the org + invoice context" and a `WidgetContext` of
`{received_invoice_id, amount, vendor_name, locale}` — i.e. the minimal form of
Pattern A only. There is no contract for argument-driven payment that records the
invoice, nor for the embedded management surface (Pattern B) or its file upload.
This ADR establishes the intended two-mode scope and the contract changes it
requires before implementation.

## Decision

One embeddable script, one **org-scoped** widget token, two modes.

1. **Widget token is organization-scoped** (not per-invoice). Signed HS256 via the
   NENE2 `TokenIssuerInterface`/`TokenVerifierInterface`, claims
   `{ scope: 'widget', org_id, iat, exp }`, server-validated. The org is derived
   solely from the token (ADR 0018); repositories keep enforcing `organization_id`
   isolation from the request-scoped holder. This **supersedes** the "org + invoice"
   token description and the payment-only widget section in the current
   `openapi.yaml`.
   - **Trust boundary**: the widget is embedded in the operator's *own
     authenticated internal system*; the org token authorizes invoice management
     for that organization. (Public-site embedding with reduced privilege /
     Origin-allowlist / short-lived tokens is a future extension, not this ADR.)

2. **Mode A — programmatic payment (host passes data).** The loader exposes a JS
   API and declarative `data-*` triggers; the host passes invoice number, payee
   (振込先) and amount. Payout **records** a received invoice and the vendor (振込先)
   on the operator's own server from those arguments, then initiates the
   gateway-hosted card payment. Recording (not pass-through) keeps the self-host
   data-ownership, immutability (ADR 0013) and audit (ADR 0011) guarantees intact.

3. **Mode B — embedded invoice management.** The widget renders a self-contained
   management UI (received-invoice list/create/edit/void, vendor/振込先 management,
   invoice image/PDF upload) scoped by the token, and pays from there. File upload
   reuses the existing invoice PDF storage.

4. **API surface** under `/api/v1/widget/*` reuses the existing domain use cases
   via a `WidgetAuthMiddleware` that validates `X-Widget-Token` and sets the org
   holder (existing Bearer/Org/Capability middleware already bypass this prefix).
   Token generation is a separate **protected** endpoint
   `POST /api/v1/widget-tokens` (normal auth + `ManageOrganizationSettings`).
   `openapi.yaml` is updated to describe this expanded surface; `WidgetContext`
   and the token semantics change accordingly.

5. **Card capture stays gateway-hosted** (ADR 0010, no PAN); records are immutable
   and audited (ADR 0011, 0013); money stays on the operator's server.

**Sequencing.** Mode A is the primary, broadly-demanded case (any system that already
holds payable data wants card payment without building a gateway integration) and is
implemented first. Mode B is a thin extension on the same org token + API and is
**partner-distribution-driven**: it pays off only where the host is itself the
distribution channel / daily workspace and switches between organizations
(e.g. an accounting-firm client portal, a franchise/association portal, or a
vertical SaaS whose domain already involves paying vendors). Where the host has no
such adjacency, standalone Payout is the better answer than embedding Mode B, so
Mode B is scoped to those partner hosts rather than offered universally.

**Out of scope (separate ADR/Issue):** OCR/LLM reading of uploaded invoices —
sending invoice images to an external cloud OCR/LLM conflicts with the self-host
data-ownership promise, so any future work must assume self-hosted inference and
amend scope-contract first. The visual-customization GUI (CSS-variable theming so
the widget matches the host) is acknowledged as core to the value proposition but
deferred to its own slice.

## Consequences

- `docs/openapi/openapi.yaml` must be revised: org-scoped token, expanded
  `/api/v1/widget/*` endpoints (context + received-invoice CRUD + vendors +
  payments + pdf), and the protected `/api/v1/widget-tokens` generation endpoint.
- `docs/explanation/{pages,features,glossary}.md`, `docs/api/endpoints.md`,
  `docs/terms.md` (register 埋め込みウィジェット / ウィジェットトークン / 埋め込みコード)
  and `docs/roadmap.md` are updated to the two-mode model.
- Reusing existing use cases keeps domain logic single-sourced; the widget's
  permission surface is defined by the exposed route set (no capability gating on
  `/widget/*`).
- HS256 tokens are stateless: revocation/rotation (denylist) is a follow-up.
- Broader surface than a pay-only widget; mitigated by the trusted-host assumption
  and repository-level org isolation. A compliance self-review
  (`docs/review/compliance.md`) accompanies the implementing PR.

## Related

- Builds on: `0009` (delegate money movement), `0010` (SAQ-A hosted capture),
  `0011` (audit), `0013` (immutability/retention), `0018` (request-based tenant
  resolution; widget org-by-token).
- Updates the widget contract in `docs/openapi/openapi.yaml` and the explanation docs.
- Out of scope here: OCR/LLM extraction (future ADR), widget visual customization (future slice).
