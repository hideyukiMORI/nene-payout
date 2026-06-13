<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPdfPathToReceivedInvoices extends AbstractMigration
{
    public function change(): void
    {
        // Local storage path of the uploaded PDF; not exposed via the API (domain-model.md).
        $this->table('received_invoices')
            ->addColumn('pdf_path', 'string', ['limit' => 1024, 'null' => true, 'default' => null, 'after' => 'vault_document_url'])
            ->update();
    }
}
