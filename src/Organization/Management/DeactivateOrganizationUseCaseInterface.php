<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;

interface DeactivateOrganizationUseCaseInterface
{
    public function execute(?string $actorUserId, string $id): Organization;
}
