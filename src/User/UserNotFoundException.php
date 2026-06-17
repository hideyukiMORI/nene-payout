<?php

declare(strict_types=1);

namespace NenePayout\User;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('User %s was not found.', $id));
    }
}
