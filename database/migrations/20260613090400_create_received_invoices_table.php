<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateReceivedInvoicesTable extends AbstractMigration
{
    public function change(): void
    {
        // Tenant-scoped; money is integer minimum currency units; soft-voided (status),
        // never hard-deleted (ADR 0013). tax_breakdown is a recorded copy (ADR 0014).
        $this->table('received_invoices', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 26, 'null' => false]) // ULID
            ->addColumn('organization_id', 'string', ['limit' => 26, 'null' => false])
            ->addColumn('vendor_id', 'string', ['limit' => 26, 'null' => false])
            ->addColumn('amount', 'biginteger', ['null' => false]) // integer minimum currency units
            ->addColumn('due_date', 'date', ['null' => false])
            ->addColumn('status', 'string', ['limit' => 16, 'null' => false, 'default' => 'pending'])
            ->addColumn('registration_number', 'string', ['limit' => 14, 'null' => true, 'default' => null])
            ->addColumn('tax_breakdown', 'text', ['null' => true, 'default' => null]) // JSON
            ->addColumn('vault_document_url', 'string', ['limit' => 1024, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'])
            ->addIndex(['organization_id', 'status'])
            ->addIndex(['organization_id', 'vendor_id'])
            ->addIndex(['organization_id', 'due_date'])
            ->create();
    }
}
