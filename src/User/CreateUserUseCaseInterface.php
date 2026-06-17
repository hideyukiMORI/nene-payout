<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

interface CreateUserUseCaseInterface
{
    public function execute(?string $actorUserId, CreateUserInput $input): User;
}
