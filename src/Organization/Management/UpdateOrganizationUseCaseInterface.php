<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;

interface UpdateOrganizationUseCaseInterface
{
    public function execute(?string $actorUserId, string $id, UpdateOrganizationInput $input): Organization;
}
