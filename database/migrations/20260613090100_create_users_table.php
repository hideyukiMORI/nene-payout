<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 26, 'null' => false]) // ULID
            ->addColumn('organization_id', 'string', ['limit' => 26, 'null' => true, 'default' => null]) // null for superadmin
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'string', ['limit' => 32, 'null' => false])
            ->addColumn('status', 'string', ['limit' => 32, 'null' => false, 'default' => 'active'])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['organization_id'])
            ->create();
    }
}
