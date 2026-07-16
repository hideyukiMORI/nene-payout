<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Closure;
use LogicException;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Support\Ulid;
use NenePayout\Vendor\VendorRepositoryInterface;

final readonly class CreateReceivedInvoiceUseCase implements CreateReceivedInvoiceUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface $invoicesFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private VendorRepositoryInterface $vendors,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $invoicesFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, CreateReceivedInvoiceInput $input): ReceivedInvoice
    {
        if ($this->vendors->findById($input->vendorId) === null) {
            throw new ValidationException([new ValidationError('vendor_id', 'Unknown vendor.', 'not_found')]);
        }

        $organizationId = $this->orgId->get();
        $id = Ulid::generate();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $input): ReceivedInvoice {
            $invoices = ($this->invoicesFactory)($exec);

            $invoices->save(new ReceivedInvoice(
                vendorId: $input->vendorId,
                amount: $input->amount,
                dueDate: $input->dueDate,
                status: ReceivedInvoiceStatus::Pending->value,
                taxBreakdown: $input->taxBreakdown,
                organizationId: $organizationId,
                registrationNumber: $input->registrationNumber,
                vaultDocumentUrl: $input->vaultDocumentUrl,
                id: $id,
            ));

            $created = $invoices->findById($id);

            if ($created === null) {
                throw new LogicException('Received invoice disappeared immediately after creation.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'received_invoice.created',
                entityType: 'received_invoice',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: null,
                after: ReceivedInvoiceResponse::toArray($created),
                id: Ulid::generate(),
            ));

            return $created;
        });
    }
}
