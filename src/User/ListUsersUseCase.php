<?php

declare(strict_types=1);

namespace NenePayout\User;

final readonly class ListUsersUseCase implements ListUsersUseCaseInterface
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    public function execute(int $limit, int $offset): ListUsersOutput
    {
        return new ListUsersOutput(
            items: $this->users->findAll($limit, $offset),
            total: $this->users->count(),
        );
    }
}
