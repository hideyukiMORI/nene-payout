<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAuditLogsTable extends AbstractMigration
{
    public function change(): void
    {
        // Append-only audit trail (ADR 0011, 0013). No UPDATE/DELETE in application code.
        $this->table('audit_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 26, 'null' => false]) // ULID
            ->addColumn('actor_user_id', 'string', ['limit' => 26, 'null' => true, 'default' => null])
            ->addColumn('organization_id', 'string', ['limit' => 26, 'null' => true, 'default' => null])
            ->addColumn('action', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('entity_type', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('entity_id', 'string', ['limit' => 26, 'null' => true, 'default' => null])
            ->addColumn('before_json', 'text', ['null' => true, 'default' => null])
            ->addColumn('after_json', 'text', ['null' => true, 'default' => null])
            ->addColumn('request_id', 'string', ['limit' => 64, 'null' => true, 'default' => null])
            ->addColumn('created_at', 'datetime', ['null' => false])
            ->addIndex(['organization_id'])
            ->addIndex(['entity_type', 'entity_id'])
            ->addIndex(['created_at'])
            ->create();
    }
}
