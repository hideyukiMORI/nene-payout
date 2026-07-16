# Audit Logging — Binding Design

**Status: binding.** Every mutating operation in NeNe Payout records **who**
changed **what**, **when**, and **how** — including the **before** and **after**
state. An auditor or reviewer must be able to reconstruct the full history of any
vendor, received invoice, payment execution, organization, or setting.

This is a compliance control (`payment-compliance.md` §9). Since #144 the
implementation is the framework audit module **`Nene2\Audit`** (NENE2 ADR 0014);
Payout no longer ships its own recorder/repository. The module is pointed at
Payout's existing `audit_logs` table (no re-migration) via an `AuditTableConfig`,
and Payout keeps only the read side (org scoping, actor-email join) and the
`payout.*`/entity action vocabulary. The compliance principles below are
unchanged — only the classes that enforce them moved into the framework.

See: [ADR 0011](../adr/0011-audit-logging.md),
[`payment-compliance.md`](./payment-compliance.md) §9,
[`multi-tenancy.md`](./multi-tenancy.md),
[ADR 0013](../adr/0013-payment-record-immutability-and-retention.md).

---

## 1. Principle (binding)

1. **Every mutating operation is audited** — create, update, void/soft-delete,
   and **every state transition** (including gateway-webhook-driven ones).
2. **Before and after are both recorded.** The field-level diff is derivable from
   the two snapshots. `before` is null for create; `after` is null for
   void/delete.
3. **Reads are never audited.**
4. **No silent mutation of the trail.** Audit rows are append-only, immutable,
   and retained for the statutory period (ADR 0013).
5. **Secrets never enter the trail** (§5).

---

## 2. What is recorded — `AuditLog` (binding)

| Field | Type | Notes |
| --- | --- | --- |
| id | ULID | Primary key |
| actor_user_id | ULID\|null | The authenticated user who performed it; null for system/webhook |
| organization_id | ULID\|null | Tenant the change belongs to (null only for cross-tenant superadmin org ops) |
| action | string | `{entity}.{verb}` — see [`terms.md` §10](../terms.md) |
| entity_type | string | Changed entity type (`vendor`, `received_invoice`, `payment_execution`, …) |
| entity_id | ULID\|null | Id of the changed entity |
| before_json | json\|null | **Sanitized** snapshot before (null for create) |
| after_json | json\|null | **Sanitized** snapshot after (null for void/delete) |
| request_id | string\|null | `X-Request-Id` correlation; physical column reused as the framework `metadata` receptacle (ADR 0014) |
| created_at | datetime | UTC instant from injected `ClockInterface` (ADR 0012) — the framework `occurredAt` field |
| actor_email | string\|null | Resolved **at read time** only (never stored) |

Records are the framework value object `Nene2\Audit\AuditEvent`, appended through
`Nene2\Audit\AuditRecorderFactoryInterface::forExecutor($exec)->record($event)`:

```php
$this->auditFactory->forExecutor($exec)->record(new \Nene2\Audit\AuditEvent(
    action: 'vendor.updated',        // free string owned by Payout (terms.md §10)
    entityType: 'vendor',
    entityId: $id,
    actorId: $actorUserId,
    organizationId: $organizationId,
    before: $beforeSnapshot,         // null for create
    after: $afterSnapshot,           // null for void/delete
    id: \NenePayout\Support\Ulid::generate(),  // ULID supplied by the call site
));
```

The framework recorder fills `occurredAt` from the injected `ClockInterface`
(UTC), so audit time is deterministic in tests and consistent with every other
"now". Because Payout's ids are ULID strings (`AuditTableConfig::$idIsAutoIncrement
= false`), the call site supplies the id; the framework does not generate it.

---

## 3. Coverage — operations that MUST record (binding)

| Domain | Actions |
| --- | --- |
| Vendor | `vendor.created` / `vendor.updated` / `vendor.deactivated` |
| Received invoice | `received_invoice.created` / `received_invoice.updated` / `received_invoice.voided` |
| Payment | `payment.initiated` / `payment.succeeded` / `payment.failed` / `payment.refunded` / `payment.charged_back` |
| Gateway settings | `gateway_settings.updated` (credentials change recorded as changed, value never stored) |
| Organization | `organization.created` / `organization.updated` / `organization.deactivated` (superadmin) |
| User | `user.created` / `user.updated` / `user.deactivated` |

- **Webhook-driven** payment transitions (`payment.succeeded`, `payment.failed`,
  `payment.refunded`, `payment.charged_back`) record with `actor_user_id = null`
  (system actor) and the gateway event correlated via `request_id`.
- Adding a new mutating operation **MUST** add its audit action here and in
  `terms.md` §10 in the same PR.

---

## 4. Where & how it is recorded (binding)

Recording happens **in the UseCase** (it has the actor, tenant, before-state, and
the business action name). Handlers pass the **actor user id** from the auth
claims (`nene2.auth.claims`); the tenant `organization_id` comes from the
resolved `RequestScopedHolder` (multi-tenancy.md).

```text
UseCase.execute(actorUserId, …):
  before  = presenter(repo.find(id))        # snapshot before (null for create)
  …perform the mutation…
  after   = presenter(repo.find(id))        # snapshot after  (null for void/delete)
  auditFactory.forExecutor(exec).record(new AuditEvent(
      action: '{entity}.{verb}', entityType, entityId: id,
      actorId: actorUserId, organizationId: orgId, before, after, id: Ulid::generate()))
```

- **before/after are built from the same `*Response` presenters used for API
  output.** This guarantees one shape and that secrets are excluded by
  construction (§5). The diff is computed from the two snapshots; we do not store
  a separate diff.

---

## 5. Sanitization (binding)

`before_json` / `after_json` are **sanitized snapshots**. They **MUST NEVER**
contain:

- card PAN, CVV, expiry, or any cardholder data (ADR 0010)
- gateway API keys, gateway/webhook secrets, payment tokens
- password hashes, raw JWTs, session identifiers
- any field not present in the entity's public `*Response` presenter

A credential change (e.g. gateway API key rotation) is recorded as *that a change
occurred* (e.g. `gateway_settings.updated` with non-secret metadata), never with
the secret value.

---

## 6. Atomicity (binding)

The mutation and its audit record **commit in a single transaction**. If the
mutation rolls back, no audit row is written; if the audit write fails, the
mutation rolls back. There is **no** best-effort, out-of-transaction audit.

Implementation pattern (NENE2): wrap the mutation in
`DatabaseTransactionManagerInterface::transactional()`, rebind the repository to
the transaction's `DatabaseQueryExecutorInterface` via its factory, and obtain a
recorder bound to the **same** executor from
`Nene2\Audit\AuditRecorderFactoryInterface::forExecutor()` so both writes share
the one transaction.

```php
return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec)
    use ($actorUserId, $orgId, $id, $input, $existing) {
    $repo = ($this->repoFactory)($exec);

    $repo->update(/* … */);
    $after = $repo->findById($id);

    $this->auditFactory->forExecutor($exec)->record(new \Nene2\Audit\AuditEvent(
        action: 'vendor.updated',
        entityType: 'vendor',
        entityId: $id,
        actorId: $actorUserId,
        organizationId: $orgId,
        before: VendorResponse::toArray($existing),
        after: VendorResponse::toArray($after),
        id: \NenePayout\Support\Ulid::generate(),
    ));

    return $after;
});
```

---

## 7. Tenant scope & immutability

- Every audit row carries `organization_id`; the read API is tenant-scoped, and
  only **superadmin** may read across tenants (multi-tenancy.md).
- The `audit_logs` table is **append-only**: no `UPDATE`, no `DELETE`. It is
  retained for the statutory period and never auto-purged (ADR 0013).

---

## 8. Read access

- `GET /admin/audit-logs` (roles: `admin` within org, `superadmin` cross-tenant)
  lists entries with filters (entity type/id, actor, action, date range) and
  pagination (`PaginationQuery`); CSV export is supported.
- `actor_email` is resolved at read time by joining the current user record; it
  is never written into the row.
- Reading audit logs is **not** itself audited.

---

## 9. Schema (reference)

```text
audit_logs
  id              ULID  PK
  actor_user_id   ULID  null
  organization_id ULID  null
  action          string(64)  not null
  entity_type     string(64)  not null
  entity_id       ULID  null
  before_json     text  null
  after_json      text  null
  request_id      string null
  created_at      datetime not null   # UTC
  index (organization_id)
  index (entity_type, entity_id)
  index (created_at)
```

See [`../development/database-standards.md`](../development/database-standards.md).

## Related

- Decision: [ADR 0011](../adr/0011-audit-logging.md)
- Compliance: [`payment-compliance.md`](./payment-compliance.md) §9
- Immutability / retention: [ADR 0013](../adr/0013-payment-record-immutability-and-retention.md)
- Action names: [`../terms.md` §10](../terms.md)
- Framework module: `Nene2\Audit` (NENE2 ADR 0014) — `vendor/hideyukimori/nene2/src/Audit/`
- Product read side: `src/Audit/` (`AuditReadRepositoryInterface`, `PdoAuditReadRepository`, list route)
