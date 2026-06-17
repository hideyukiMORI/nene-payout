<?php

declare(strict_types=1);

namespace NenePayout\Organization;

final readonly class UpdateOrganizationInput
{
    public function __construct(
        public string $name,
    ) {
    }
}
