<?php

declare(strict_types=1);

namespace NenePayout\User;

interface ListUsersUseCaseInterface
{
    public function execute(int $limit, int $offset): ListUsersOutput;
}
