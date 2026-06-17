<?php

declare(strict_types=1);

namespace NenePayout\User;

use RuntimeException;

final class UserEmailConflictException extends RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('A user with email %s already exists.', $email));
    }
}
