<?php

declare(strict_types=1);

namespace NenePayout\Organization;

interface GetOrganizationUseCaseInterface
{
    /** Returns the current tenant's organization (resolved from request scope). */
    public function execute(): Organization;
}
