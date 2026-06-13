<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\Support\Ulid;

final readonly class CreateVendorUseCase implements CreateVendorUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface $vendorsFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $vendorsFactory,
        private Closure $auditFactory,
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

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'vendor.created',
                'vendor',
                $id,
                null,
                VendorResponse::toArray($created),
            );

            return $created;
        });
    }
}
