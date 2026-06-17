<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Nene2\Http\RequestScopedHolder;

final readonly class GetOrganizationUseCase implements GetOrganizationUseCaseInterface
{
    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function execute(): Organization
    {
        $organizationId = $this->orgId->get();
        $organization = $this->organizations->findById($organizationId);

        if ($organization === null) {
            throw new OrganizationNotFoundException($organizationId);
        }

        return $organization;
    }
}
