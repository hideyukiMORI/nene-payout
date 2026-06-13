<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorderInterface;

final readonly class VoidReceivedInvoiceUseCase implements VoidReceivedInvoiceUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface $invoicesFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private ReceivedInvoiceRepositoryInterface $invoices,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $invoicesFactory,
        private Closure $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, string $id): ReceivedInvoice
    {
        $existing = $this->invoices->findById($id);

        if ($existing === null) {
            throw new ReceivedInvoiceNotFoundException($id);
        }

        // Voidable only before payment has started (ADR 0013).
        if ($existing->status !== ReceivedInvoiceStatus::Pending->value) {
            throw new InvoiceNotEditableException($existing->status);
        }

        $organizationId = $this->orgId->get();

        $voided = new ReceivedInvoice(
            vendorId: $existing->vendorId,
            amount: $existing->amount,
            dueDate: $existing->dueDate,
            status: ReceivedInvoiceStatus::Voided->value,
            taxBreakdown: $existing->taxBreakdown,
            organizationId: $organizationId,
            registrationNumber: $existing->registrationNumber,
            vaultDocumentUrl: $existing->vaultDocumentUrl,
            id: $id,
            createdAt: $existing->createdAt,
        );

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $existing, $voided): ReceivedInvoice {
            $invoices = ($this->invoicesFactory)($exec);
            $invoices->update($voided);

            // Soft void: `after` is null (ADR 0011).
            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'received_invoice.voided',
                'received_invoice',
                $id,
                ReceivedInvoiceResponse::toArray($existing),
                null,
            );

            return $voided;
        });
    }
}
