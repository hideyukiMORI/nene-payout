<?php

declare(strict_types=1);

namespace NenePayout\Organization;

interface UpdateOrganizationUseCaseInterface
{
    /** Updates the current tenant's organization settings (name only). */
    public function execute(?string $actorUserId, UpdateOrganizationInput $input): Organization;
}
