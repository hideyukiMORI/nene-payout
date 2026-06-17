<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;

final readonly class GetOrganizationUseCase implements GetOrganizationUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function execute(string $id): Organization
    {
        $organization = $this->organizations->findById($id);

        if ($organization === null) {
            throw new OrganizationNotFoundException($id);
        }

        return $organization;
    }
}
