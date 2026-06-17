<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use NenePayout\Organization\Organization;

final readonly class ListOrganizationsOutput
{
    /**
     * @param list<Organization> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
