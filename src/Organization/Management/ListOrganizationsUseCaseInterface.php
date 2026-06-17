<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

interface ListOrganizationsUseCaseInterface
{
    public function execute(int $limit, int $offset): ListOrganizationsOutput;
}
