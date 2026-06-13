<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVendorsTable extends AbstractMigration
{
    public function change(): void
    {
        // Tenant-scoped; soft-deactivated (is_active), never hard-deleted (ADR 0013).
        $this->table('vendors', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 26, 'null' => false]) // ULID
            ->addColumn('organization_id', 'string', ['limit' => 26, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('bank_code', 'string', ['limit' => 4, 'null' => false])
            ->addColumn('branch_code', 'string', ['limit' => 3, 'null' => false])
            ->addColumn('account_type', 'string', ['limit' => 8, 'null' => false]) // 普通 / 当座
            ->addColumn('account_number', 'string', ['limit' => 7, 'null' => false])
            ->addColumn('account_name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('registration_number', 'string', ['limit' => 14, 'null' => true, 'default' => null]) // T + 13
            ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'])
            ->addIndex(['organization_id', 'is_active'])
            ->create();
    }
}
