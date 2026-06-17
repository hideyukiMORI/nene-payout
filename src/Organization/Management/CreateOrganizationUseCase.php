<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\OrganizationResponse;
use NenePayout\Support\Ulid;

final readonly class CreateOrganizationUseCase implements CreateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     */
    public function __construct(
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private Closure $auditFactory,
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
            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $id,
                'organization.created',
                'organization',
                $id,
                null,
                OrganizationResponse::toArray($created),
            );

            return $created;
        });
    }
}
