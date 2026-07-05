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
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\Ulid;

final readonly class CreateOrganizationUseCase implements CreateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private AuditRecorderFactoryInterface $auditFactory,
    ) {
    }

    public function execute(?string $actorUserId, CreateOrganizationInput $input): Organization
    {
        $id = Ulid::generate();

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $id, $input): Organization {
            $organizations = ($this->organizationsFactory)($exec);

            if ($organizations->existsBySlug($input->slug)) {
                throw OrganizationSlugConflictException::forSlug($input->slug);
            }

            if ($input->customDomain !== null && $organizations->existsByCustomDomain($input->customDomain)) {
                throw OrganizationSlugConflictException::forCustomDomain($input->customDomain);
            }

            $organizations->save(new Organization(
                slug: $input->slug,
                name: $input->name,
                isActive: true,
                id: $id,
                customDomain: $input->customDomain,
            ));

            $created = $organizations->findById($id);

            if ($created === null) {
                throw new LogicException('Organization disappeared immediately after creation.');
            }

            // Audit is scoped to the target organization (the one created).
            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'organization.created',
                entityType: 'organization',
                entityId: $id,
                actorId: $actorUserId,
                organizationId: $id,
                before: null,
                after: OrganizationResponse::toArray($created),
                id: Ulid::generate(),
            ));

            return $created;
        });
    }
}
