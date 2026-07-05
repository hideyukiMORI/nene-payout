<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Support\Ulid;

final readonly class DeactivateVendorUseCase implements DeactivateVendorUseCaseInterface
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

    public function execute(?string $actorUserId, string $id): Vendor
    {
        $existing = $this->vendors->findById($id);

        if ($existing === null) {
            throw new VendorNotFoundException($id);
        }

        $organizationId = $this->orgId->get();

        $deactivated = new Vendor(
            name: $existing->name,
            bankCode: $existing->bankCode,
            branchCode: $existing->branchCode,
            accountType: $existing->accountType,
            accountNumber: $existing->accountNumber,
            accountName: $existing->accountName,
            isActive: false,
            organizationId: $organizationId,
            registrationNumber: $existing->registrationNumber,
            id: $id,
            createdAt: $existing->createdAt,
        );

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $existing, $deactivated): Vendor {
            $vendors = ($this->vendorsFactory)($exec);
            $vendors->update($deactivated);

            // Soft delete: `after` is null (ADR 0011 / audit-logging.md).
            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'vendor.deactivated',
                entityType: 'vendor',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: VendorResponse::toArray($existing),
                after: null,
                id: Ulid::generate(),
            ));

            return $deactivated;
        });
    }
}
