<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use RuntimeException;

final class OrganizationNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Organization %s was not found.', $id));
    }
}
