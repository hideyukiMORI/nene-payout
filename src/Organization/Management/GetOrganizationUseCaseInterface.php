<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;

interface GetOrganizationUseCaseInterface
{
    public function execute(string $id): Organization;
}
