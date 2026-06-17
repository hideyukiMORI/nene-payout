<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

interface GetUserUseCaseInterface
{
    public function execute(string $id): User;
}
