<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use LogicException;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\Ulid;

final readonly class UpdateOrganizationUseCase implements UpdateOrganizationUseCaseInterface
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

    public function execute(?string $actorUserId, string $id, UpdateOrganizationInput $input): Organization
    {
        $existing = $this->organizations->findById($id);

        if ($existing === null) {
            throw new OrganizationNotFoundException($id);
        }

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $id, $input, $existing): Organization {
            $organizations = ($this->organizationsFactory)($exec);

            // A custom domain may only move to one not already taken by another org.
            if (
                $input->customDomain !== null
                && $input->customDomain !== $existing->customDomain
                && $organizations->existsByCustomDomain($input->customDomain)
            ) {
                throw OrganizationSlugConflictException::forCustomDomain($input->customDomain);
            }

            // slug is immutable; is_active is unchanged by update (use deactivate).
            $organizations->update(new Organization(
                slug: $existing->slug,
                name: $input->name,
                isActive: $existing->isActive,
                id: $id,
                customDomain: $input->customDomain,
                createdAt: $existing->createdAt,
            ));

            $updated = $organizations->findById($id);

            if ($updated === null) {
                throw new LogicException('Organization disappeared immediately after update.');
            }

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.updated',
                entityType: 'organization',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $id,
                before: OrganizationResponse::toArray($existing),
                after: OrganizationResponse::toArray($updated),
                id: Ulid::generate(),
            ));

            return $updated;
        });
    }
}
