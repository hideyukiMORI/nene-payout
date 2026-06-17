<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

final readonly class ListUsersOutput
{
    /**
     * @param list<User> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
