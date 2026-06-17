<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\OrganizationRepositoryInterface;

final readonly class ListOrganizationsUseCase implements ListOrganizationsUseCaseInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
    ) {
    }

    public function execute(int $limit, int $offset): ListOrganizationsOutput
    {
        return new ListOrganizationsOutput(
            items: $this->organizations->findAll($limit, $offset),
            total: $this->organizations->count(),
        );
    }
}
