<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;

interface CreateOrganizationUseCaseInterface
{
    public function execute(?string $actorUserId, CreateOrganizationInput $input): Organization;
}
