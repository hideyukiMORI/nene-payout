<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Closure;
use LogicException;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Support\Ulid;

final readonly class UpdateVendorUseCase implements UpdateVendorUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface $vendorsFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private VendorRepositoryInterface $vendors,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $vendorsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, string $id, UpdateVendorInput $input): Vendor
    {
        $existing = $this->vendors->findById($id);

        if ($existing === null) {
            throw new VendorNotFoundException($id);
        }

        $organizationId = $this->orgId->get();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $input, $existing): Vendor {
            $vendors = ($this->vendorsFactory)($exec);

            $vendors->update(new Vendor(
                name: $input->name,
                bankCode: $input->bankCode,
                branchCode: $input->branchCode,
                accountType: $input->accountType,
                accountNumber: $input->accountNumber,
                accountName: $input->accountName,
                isActive: true,
                organizationId: $organizationId,
                registrationNumber: $input->registrationNumber,
                id: $id,
            ));

            $updated = $vendors->findById($id);

            if ($updated === null) {
                throw new LogicException('Vendor disappeared immediately after update.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'vendor.updated',
                entityType: 'vendor',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: VendorResponse::toArray($existing),
                after: VendorResponse::toArray($updated),
                id: Ulid::generate(),
            ));

            return $updated;
        });
    }
}
