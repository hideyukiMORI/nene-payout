<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\Ulid;

final readonly class DeactivateOrganizationUseCase implements DeactivateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
    ) {
    }

    public function execute(?string $actorUserId, string $id): Organization
    {
        $existing = $this->organizations->findById($id);

        if ($existing === null) {
            throw new OrganizationNotFoundException($id);
        }

        $deactivated = new Organization(
            slug: $existing->slug,
            name: $existing->name,
            isActive: false,
            id: $id,
            customDomain: $existing->customDomain,
            createdAt: $existing->createdAt,
        );

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $id, $existing, $deactivated): Organization {
            $organizations = ($this->organizationsFactory)($exec);
            $organizations->update($deactivated);

            // Soft delete: `after` is null (ADR 0011 / audit-logging.md).
            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.deactivated',
                entityType: 'organization',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $id,
                before: OrganizationResponse::toArray($existing),
                after: null,
                id: Ulid::generate(),
            ));

            return $deactivated;
        });
    }
}
