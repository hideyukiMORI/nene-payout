<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePaymentExecutionsTable extends AbstractMigration
{
    public function change(): void
    {
        // Money is integer minimum currency units; amount / charge_amount / processing_fee
        // are distinct (ADR 0015). Terminal records are immutable (ADR 0013).
        $this->table('payment_executions', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 26, 'null' => false]) // ULID
            ->addColumn('organization_id', 'string', ['limit' => 26, 'null' => false])
            ->addColumn('received_invoice_id', 'string', ['limit' => 26, 'null' => false])
            ->addColumn('amount', 'biginteger', ['null' => false])
            ->addColumn('charge_amount', 'biginteger', ['null' => true, 'default' => null])
            ->addColumn('processing_fee', 'biginteger', ['null' => true, 'default' => null])
            ->addColumn('gateway', 'string', ['limit' => 32, 'null' => false])
            ->addColumn('gateway_reference', 'string', ['limit' => 255, 'null' => true, 'default' => null])
            ->addColumn('status', 'string', ['limit' => 16, 'null' => false])
            ->addColumn('initiated_at', 'datetime', ['null' => false])
            ->addColumn('completed_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['organization_id'])
            ->addIndex(['organization_id', 'received_invoice_id'])
            ->addIndex(['organization_id', 'status'])
            ->create();
    }
}
