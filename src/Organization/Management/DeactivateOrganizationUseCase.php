<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;

final readonly class DeactivateOrganizationUseCase implements DeactivateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private Closure $auditFactory,
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
            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $id,
                'organization.deactivated',
                'organization',
                $id,
                OrganizationResponse::toArray($existing),
                null,
            );

            return $deactivated;
        });
    }
}
