<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;

final readonly class UpdateOrganizationUseCase implements UpdateOrganizationUseCaseInterface
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

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $id,
                'organization.updated',
                'organization',
                $id,
                OrganizationResponse::toArray($existing),
                OrganizationResponse::toArray($updated),
            );

            return $updated;
        });
    }
}
