# Database & Schema Standards — NeNe Payout

**Binding.** How to design schema, write migrations, connect, query, and run
transactions. Inherits NENE2 (`database-migrations.md`, `domain-layer.md`,
`test-database-strategy.md`); this file records Payout-specific rules.

## Layout

```
database/
├── migrations/      # Phinx, versioned schema changes
├── seeds/           # local/dev seed data (no secrets, no private data)
└── schema/          # schema snapshots / ERD for review
```

Framework core stays database-independent. Tier A = SQLite, Tier B = MySQL
(ADR 0003) — write **SQLite-compatible SQL**; no MySQL-only syntax in core.

## Connection & query (binding)

- Repositories depend on **`DatabaseQueryExecutorInterface`** (constructor
  injection) for all reads/writes. **No raw `PDO`**, no singletons, no
  `getInstance()`.
- Connections are built by `PdoConnectionFactory` from typed `DatabaseConfig`;
  app code never reads `getenv()` or constructs `new PDO(...)`.
- Multi-query atomic work uses **`DatabaseTransactionManagerInterface`** — never
  hand-written `BEGIN`/`COMMIT` strings.
- All SQL lives inside `PdoXxxRepository`. Use cases and domain objects contain
  no SQL. Cast row values to typed PHP values on the way out.

```php
final class PdoVendorRepository implements VendorRepositoryInterface
{
    public function __construct(
        private readonly DatabaseQueryExecutorInterface $query,
    ) {}

    public function findById(string $id, string $organizationId): ?Vendor
    {
        $row = $this->query->fetchOne(
            'SELECT id, name, bank_code FROM vendors WHERE id = ? AND organization_id = ?',
            [$id, $organizationId],
        );
        return $row !== null ? new Vendor(/* typed cast */) : null;
    }
}
```

## Migrations (binding)

- Tool: **Phinx** (`database/migrations/`). Do not build a custom runner.
- File name: time-sortable `YYYYMMDDHHMMSS_describe_change.php`
  (e.g. `20260613120000_create_vendors_table.php`).
- Every migration defines a rollback when the tool supports it; if a change
  cannot be safely rolled back, document why in the migration and the PR.
- Split destructive data changes from schema changes when practical.
- A new entity that introduces a new table requires a migration **and** a schema
  snapshot in `database/schema/` (endpoint-scaffold step).
- Commands (via Composer): `migrations:status` / `migrations:migrate` /
  `migrations:rollback` / `migrations:seed`. `composer check` does not run
  migrations.

## Schema conventions (binding)

| Convention | Rule |
| --- | --- |
| Table names | `snake_case`, plural (`vendors`, `received_invoices`, `payment_executions`, `audit_logs`) |
| Column names | `snake_case` (`organization_id`, `bank_code`, `account_name`) |
| Primary key | `id` — **ULID** stored as string |
| Timestamps | `created_at`, `updated_at` — **UTC** instants (ADR 0012) |
| Tenant scope | **every** tenant table has `organization_id`; every query filters by it (ADR 0004) |
| Money | **integer minimum currency units**; no float, no `DECIMAL` (payment-compliance §6) |
| Calendar dates | JST calendar dates for `due_date` (ADR 0012) |

## Compliance-driven schema rules (binding)

These come from `docs/explanation/payment-compliance.md` and its ADRs:

- **Soft delete / void only** — financial tables (`received_invoices`,
  `payment_executions`, vendor snapshots used in a payment) carry void/soft-delete
  columns (e.g. `voided_at`). **No hard `DELETE`** on financial data (ADR 0013).
- **Immutable terminal records** — a `PaymentExecution` in `succeeded`/`failed`
  is not updated in place; refunds/chargebacks are **new linked records**
  (`status` `refunded` / `charged_back`) (ADR 0013, 0015).
- **Distinct amounts** — store `amount`, `charge_amount`, `processing_fee`
  separately; never conflate (ADR 0015).
- **Tax fields are recorded copies** — `registration_number` (`^T[0-9]{13}$`,
  syntax-only), `tax_rate_bps` (`1000`/`800`), `taxable_amount`, `tax_amount`;
  Payout never computes deductions (ADR 0014).
- **Audit** — `audit_logs` table per ADR 0011 / [`../explanation/audit-logging.md`](../explanation/audit-logging.md):
  every mutating op records actor / before_json / after_json / request_id;
  **append-only** (no UPDATE/DELETE); indexes on `organization_id`,
  `(entity_type, entity_id)`, `created_at`; the mutation and its audit row commit
  in **one transaction** (`DatabaseTransactionManagerInterface`); never store
  tokens, API keys, secrets, or PAN.
- **Retention** — no auto-purge of financial records before the statutory period
  (generally 7y, up to 10y).

All identifiers above must match [`../terms.md`](../terms.md). New identifiers →
update `terms.md` in the same PR.

## Testing

- Use-case tests run **without** a database via in-memory repository doubles
  (implement the `RepositoryInterface`, live in `tests/`, never shipped).
- Adapter tests exercise real SQL via the database test command
  (`composer test:database` / `:mysql`), covering SQL correctness, type casting,
  and tenant-filter edge cases.

## Related

- Runtime objects: [`nene2-runtime-reference.md`](./nene2-runtime-reference.md)
- Layering: [`backend-standards.md`](./backend-standards.md)
- Compliance: [`../explanation/payment-compliance.md`](../explanation/payment-compliance.md)
