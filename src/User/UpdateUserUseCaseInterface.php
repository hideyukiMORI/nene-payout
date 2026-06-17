<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

interface UpdateUserUseCaseInterface
{
    public function execute(?string $actorUserId, string $id, UpdateUserInput $input): User;
}
