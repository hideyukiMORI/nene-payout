<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\ReceivedInvoice\Pdf\PdfStorageInterface;
use Psr\Http\Message\UploadedFileInterface;

final readonly class AttachReceivedInvoicePdfUseCase implements AttachReceivedInvoicePdfUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface $invoicesFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private ReceivedInvoiceRepositoryInterface $invoices,
        private PdfStorageInterface $storage,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $invoicesFactory,
        private Closure $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, string $id, UploadedFileInterface $file): ReceivedInvoice
    {
        $existing = $this->invoices->findById($id);

        if ($existing === null) {
            throw new ReceivedInvoiceNotFoundException($id);
        }

        $organizationId = $this->orgId->get();

        // File is written first (filesystem is non-transactional); the DB pointer
        // and its audit row then commit atomically.
        $pdfPath = $this->storage->store($organizationId, $id, $file);

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $existing, $pdfPath): ReceivedInvoice {
            $invoices = ($this->invoicesFactory)($exec);
            $invoices->attachPdf($id, $pdfPath);

            $updated = $invoices->findById($id);

            if ($updated === null) {
                throw new LogicException('Received invoice disappeared immediately after attaching PDF.');
            }

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'received_invoice.pdf_attached',
                'received_invoice',
                $id,
                ReceivedInvoiceResponse::toArray($existing),
                ReceivedInvoiceResponse::toArray($updated),
            );

            return $updated;
        });
    }
}
