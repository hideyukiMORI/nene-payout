<?php

declare(strict_types=1);

namespace NenePayout\User;

use NenePayout\Auth\User;

final readonly class GetUserUseCase implements GetUserUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(string $id): User
    {
        $user = $this->users->findById($id);

        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }
}
