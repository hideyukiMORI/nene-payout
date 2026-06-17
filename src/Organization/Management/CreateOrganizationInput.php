<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

final readonly class CreateOrganizationInput
{
    public function __construct(
        public string $slug,
        public string $name,
        public ?string $customDomain,
    ) {
    }
}
