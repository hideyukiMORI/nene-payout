# Canonical Terms — NeNe Payout

**This file is the single source of truth (唯一の真実) for every identifier and
canonical spelling in this repository. It is binding (ADR 0017).**

This is the **only** file allowed to define the canonical spelling of an
identifier. Every other document (including `glossary.md`) must defer to it and
must never introduce a competing spelling.

## The rule — zero typos, strict enforcement (binding)

1. **Exact match, everywhere.** Every identifier in code, API/JSON, DB,
   tests, OpenAPI, docs, commit scopes, and branch names **MUST** match an entry
   in this file **character-for-character** (case, separators, and spelling).
2. **No unregistered names.** Introducing or renaming any identifier **MUST**
   update this file in the **same PR**. An identifier not registered here is a
   defect.
3. **Typos and 表記ゆれ block merge — no exceptions.** A mismatch (typo, wrong
   case, camelCase vs snake_case, `Nene` vs `NeNe`, etc.) is a merge blocker.
   Reviewers **MUST** reject it. There is no "fix later".
4. **One spelling per concept.** If a concept needs a new name, register it here
   first, then use only that spelling. Never ship two spellings for one thing.
5. **Deletions/renames are tracked.** Renaming an identifier updates this file
   and every usage in the same PR; the old spelling must not remain anywhere.

### How to verify (before every PR)

- Self-check identifiers against this file (`docs/development/self-review.md`).
- Grep for the spelling you are introducing and confirm it matches an entry:
  ```bash
  git grep -n "<identifier>"
  ```
- A CI term-lint check is a planned follow-up; until then, this is a manual,
  reviewer-enforced gate.

## Product & domain names (canonical spelling)

Use these exact spellings — never `Nene`, `nene Payout`, `NenePayout` (in prose),
or other variants in docs/UI.

| Canonical | Use | Wrong |
| --- | --- | --- |
| `NeNe Payout` | Product display name (prose, UI, docs) | `Nene Payout`, `NENE Payout`, `NenePayout` |
| `NeNe Invoice` / `NeNe Clear` / `NeNe Vault` | Sibling product display names | `Nene *`, `nene-*` in prose |
| `NENE2` | Framework display name **in prose** | `Nene2` / `nene2` as prose name |
| `Nene2\` | NENE2 PHP namespace root (code) — correct as-is | writing `NENE2\` in code |
| `nene-payout` | Repository / composer package id | `nene_payout` (that is the DB prefix only) |

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
| `UseCase` / `UseCaseInterface` | Business logic (NOT `Service`); method `execute` |
| `RepositoryInterface` | Repository abstraction |
| `PdoXxxRepository` | PDO implementation |
| `Input` / `Output` | Readonly use-case DTOs |
| `InputMapper` | Request → Input DTO mapper |
| `GatewayAdapter` | Payment gateway adapter (e.g. `StripeGatewayAdapter`) |
| `ServiceProvider` | DI registration per domain concept |
| `Middleware` | PSR-15 middleware (e.g. `OrgResolverMiddleware`, `CapabilityMiddleware`) |
| `ResolutionStrategy` | Tenant resolution strategy (e.g. `SubdomainResolutionStrategy`) |
| `Exception` | Named domain exception |
| `Status` | Backed enum for entity status (e.g. `ReceivedInvoiceStatus`) |

## §3 Entities

| Identifier | Type | Notes |
| --- | --- | --- |
| `Organization` | Entity | テナント（`organizations` テーブル：`slug` / `custom_domain` / `is_active`） — ADR 0004, 0018 |
| `ReceivedInvoice` | Entity | 受け取った請求書 |
| `Vendor` | Entity | 支払先の仕入先・外注先 |
| `PaymentExecution` | Entity | 決済実行記録 |
| `AuditLog` | Entity | 監査ログ（`audit_logs` テーブル） — ADR 0011 |

## §4 Status values

### ReceivedInvoice.status
| Value | Meaning |
| --- | --- |
| `pending` | 未払い |
| `processing` | 決済中 |
| `paid` | 支払い完了 |
| `failed` | 失敗 |
| `voided` | 無効化（論理削除 — ADR 0013） |

### PaymentExecution.status
| Value | Meaning |
| --- | --- |
| `initiated` | 開始済み |
| `succeeded` | 成功 |
| `failed` | 失敗 |
| `refunded` | 返金（新規リンクレコード — ADR 0013, 0015） |
| `charged_back` | チャージバック（新規リンクレコード — ADR 0013, 0015） |

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
| `amount` | Integer cents — invoice amount the vendor is owed/receives |
| `charge_amount` | Integer cents — amount the operator's card is charged (ADR 0015) |
| `processing_fee` | Integer cents — card-payment processing fee (ADR 0015) |
| `due_date` | ISO 8601 date string (JST calendar date — ADR 0012) |
| `bank_code` | |
| `branch_code` | |
| `account_type` | |
| `account_number` | |
| `account_name` | |
| `registration_number` | Vendor 適格請求書発行事業者 登録番号; format `^T[0-9]{13}$`, syntax-only (ADR 0014) |
| `tax_rate_bps` | Tax rate in basis points; allowed `1000` (10%) / `800` (8% reduced) |
| `taxable_amount` | Integer cents — taxable amount for a tax-rate group (recorded copy) |
| `tax_amount` | Integer cents — consumption tax for a tax-rate group (recorded copy) |
| `vault_document_url` | HTTP reference to a nene-vault document (ADR 0014) |
| `invoice_client_url` | HTTP reference to a nene-invoice client |
| `gateway` | Gateway identifier (§6) |
| `gateway_reference` | Gateway transaction / reference id |
| `initiated_at` | UTC instant (ADR 0012) |
| `completed_at` | UTC instant (ADR 0012) |

## §8 Env variables

| Variable | Notes |
| --- | --- |
| `NENE_PAYOUT_PORT` | HTTP port (default 9000) — 90 lane, see `development/local-ports.md` |
| `NENE_PAYOUT_FRONTEND_PORT` | Vite port (default 5190) |
| `NENE_PAYOUT_MYSQL_PORT` | MySQL port (default 3400) |
| `NENE_PAYOUT_PHPMYADMIN_PORT` | phpMyAdmin port (default 9001) |
| `NENE2_LOCAL_JWT_SECRET` | Local JWT HS256 secret (NENE2 `LocalBearerTokenVerifier`) |
| `TENANT_RESOLUTION` | Tenant resolution mode: `single` / `subdomain` / `path` / `custom_domain` (ADR 0018) |
| `BASE_DOMAIN` | Base domain for subdomain resolution (e.g. `pay.example.com`) |
| `ORG_SLUG` | Organization slug for `single` mode (Tier A / dev) |

## §9 Prohibited spellings

| Wrong | Correct |
| --- | --- |
| `Controller` | `Handler` |
| `Service` | `UseCase` |
| `Repo` | `RepositoryInterface` |
| `orgId` | `organization_id` |
| `vendorId` | `vendor_id` |
| `invoiceId` | `received_invoice_id` |

## §10 Audit log — actions & fields (ADR 0011, audit-logging.md)

Action format: `{entity}.{verb}` (snake_case entity, past-tense verb).

| Action | Meaning |
| --- | --- |
| `vendor.created` / `vendor.updated` / `vendor.deactivated` | Vendor mutations |
| `received_invoice.created` / `received_invoice.updated` / `received_invoice.voided` | Invoice mutations |
| `payment.initiated` | Payment instruction sent to gateway |
| `payment.succeeded` / `payment.failed` | Gateway result recorded |
| `payment.refunded` / `payment.charged_back` | Post-settlement events (linked records) |
| `gateway_settings.updated` | Gateway configuration change (secret value never stored) |
| `organization.created` / `organization.updated` / `organization.deactivated` | Organization mutations (superadmin) |
| `user.created` / `user.updated` / `user.deactivated` | User mutations |

### `audit_logs` fields
| Field | Notes |
| --- | --- |
| `actor_user_id` | Acting user (ULID); null for system/webhook |
| `organization_id` | Tenant scope (ULID) |
| `action` | `{entity}.{verb}` |
| `entity_type` | Changed entity type (`vendor`, `received_invoice`, …) |
| `entity_id` | Changed entity id (ULID) |
| `before_json` | Sanitized snapshot before (null for create) |
| `after_json` | Sanitized snapshot after (null for void/delete) |
| `request_id` | `X-Request-Id` correlation |
| `created_at` | UTC instant |
| `actor_email` | Resolved at read time only — never stored |

## §11 Roles & capabilities (ADR 0004, multi-tenancy.md)

### Roles
| Value | Scope |
| --- | --- |
| `superadmin` | Cross-tenant (Suite orchestration / org management only) |
| `admin` | Full access within own organization |
| `operator` | Create invoices & initiate payments; no settings |

### Capabilities
| Identifier | Meaning |
| --- | --- |
| `ManageOrganizations` | Cross-tenant org management (superadmin) |
| `ManageGatewaySettings` | Gateway configuration |
| `ManageVendors` | Vendor management |
| `ManageOrganizationSettings` | Organization settings |
| `RegisterInvoice` | Register received invoices |
| `InitiatePayment` | Initiate card payment |
| `ViewPayments` | View payment history |

## §12 Request attributes (tenant context)

| Attribute | Value |
| --- | --- |
| `nene2.org.id` | Resolved `organization_id` (ULID) |
| `nene2.org.slug` | Resolved organization slug |
| `nene2.auth.credential_type` | `"bearer"` (NENE2 auth) |
| `nene2.auth.claims` | Decoded JWT claims (user id, role) |

## §13 i18n (i18n.md)

| Identifier | Notes |
| --- | --- |
| `ja` / `en` | `SupportedLocale` values (Japanese / English) |
| `nene-payout-locale` | `localStorage` key for the persisted locale |
| `MessageCatalog` | Type from `messages/en.ts` — source of truth for message **keys** |
| `common.*` / `admin.{feature}.{element}` / `widget.*` | Message key namespaces; params `{{name}}` |

> Message keys live in `frontend/src/shared/i18n/messages/en.ts` (the typed
> source of truth), not in this file; `terms.md` governs code/API/DB identifiers.

## §14 Frontend (FSD — frontend-standards.md, ADR 0019)

### Layers (import order: `app → pages → features → entities → shared`)
`app` / `pages` / `features` / `entities` / `shared`

### Entity resource slugs (kebab-case = OpenAPI tag)
`received-invoice` / `vendor` / `payment-execution` / `organization` /
`gateway-setting` / `user` / `audit-log`

### Entity file set (per `entities/{resource}/`)
`index.ts` / `ids.ts` / `enum.ts` / `api-types.ts` / `model.ts` / `mapper.ts` /
`query-keys.ts` / `queries.ts` / `mutations.ts`

### File-name casing
Components `PascalCase.tsx`; hooks `use-kebab-case.ts`; other modules `kebab-case.ts`;
component props `{Component}Props`. Named exports only (no default exports).

## §15 API pagination & envelope (NENE2; openapi.yaml)

| Identifier | Notes |
| --- | --- |
| `limit` / `offset` | List query params (NENE2 `PaginationQueryParser`) |
| `items` / `limit` / `offset` / `total` | List envelope fields (`PaginationResponse`); `total` optional |
| `/api/v1` | Versioned API base path; collections plural kebab-case (`received-invoices`, `payment-executions`, `gateway-settings`, `audit-logs`) |

## §16 Problem Details types (RFC 9457; base `https://nene-payout.dev/problems/`)

| Slug | When |
| --- | --- |
| `validation-failed` | 422 — request validation (`errors[]`) |
| `unauthorized` | 401 |
| `forbidden` | 403 — insufficient capability |
| `not-found` | 404 |
| `conflict` | 409 — duplicate / non-permitted transition |
| `payload-too-large` | 413 |
| `internal-server-error` | 500 |
| `org-not-resolved` / `org-not-found` / `org-inactive` | tenant resolution (ADR 0018) |
| `invoice-not-editable` | 409 — edit attempted when not `pending` |
| `payment-not-allowed` | 409 — invoice not payable in current status |
| `webhook-signature-invalid` | 400 — gateway webhook signature check failed |
| `gateway-error` | upstream gateway failure |
