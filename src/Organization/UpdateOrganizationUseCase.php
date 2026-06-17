<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorderInterface;

final readonly class UpdateOrganizationUseCase implements UpdateOrganizationUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface $organizationsFactory
     * @param Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface $auditFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $organizationsFactory,
        private Closure $auditFactory,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(?string $actorUserId, UpdateOrganizationInput $input): Organization
    {
        $organizationId = $this->orgId->get();
        $existing = $this->organizations->findById($organizationId);

        if ($existing === null) {
            throw new OrganizationNotFoundException($organizationId);
        }

        return $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $input, $existing): Organization {
            $organizations = ($this->organizationsFactory)($exec);

            // Only the name is mutable here; slug/custom_domain stay as resolved.
            $organizations->update(new Organization(
                slug: $existing->slug,
                name: $input->name,
                isActive: $existing->isActive,
                id: $organizationId,
                customDomain: $existing->customDomain,
                createdAt: $existing->createdAt,
            ));

            $updated = $organizations->findById($organizationId);

            if ($updated === null) {
                throw new LogicException('Organization disappeared immediately after update.');
            }

            ($this->auditFactory)($exec)->record(
                $actorUserId,
                $organizationId,
                'organization.updated',
                'organization',
                $organizationId,
                OrganizationResponse::toArray($existing),
                OrganizationResponse::toArray($updated),
            );

            return $updated;
        });
    }
}
