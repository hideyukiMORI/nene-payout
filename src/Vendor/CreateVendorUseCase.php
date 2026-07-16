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

final readonly class CreateVendorUseCase implements CreateVendorUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface $vendorsFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $vendorsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, CreateVendorInput $input): Vendor
    {
        $organizationId = $this->orgId->get();
        $id = Ulid::generate();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $id, $input): Vendor {
            $vendors = ($this->vendorsFactory)($exec);

            $vendors->save(new Vendor(
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

            $created = $vendors->findById($id);

            if ($created === null) {
                throw new LogicException('Vendor disappeared immediately after creation.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'vendor.created',
                entityType: 'vendor',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: null,
                after: VendorResponse::toArray($created),
                id: Ulid::generate(),
            ));

            return $created;
        });
    }
}
