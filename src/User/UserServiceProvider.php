<?php

declare(strict_types=1);

namespace NenePayout\User;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Support\ServiceProviderSupport;
use Psr\Container\ContainerInterface;

final readonly class UserServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                UserRepositoryInterface::class,
                static fn (ContainerInterface $c): UserRepositoryInterface
                    => new PdoUserRepository(self::query($c), self::orgHolder($c), self::clock($c)),
            )
            ->set(
                ListUsersUseCaseInterface::class,
                static fn (ContainerInterface $c): ListUsersUseCase
                    => new ListUsersUseCase(self::service($c, UserRepositoryInterface::class)),
            )
            ->set(
                GetUserUseCaseInterface::class,
                static fn (ContainerInterface $c): GetUserUseCase
                    => new GetUserUseCase(self::service($c, UserRepositoryInterface::class)),
            )
            ->set(
                CreateUserUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateUserUseCase => new CreateUserUseCase(
                    self::tx($c),
                    self::usersFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                UpdateUserUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateUserUseCase => new UpdateUserUseCase(
                    self::service($c, UserRepositoryInterface::class),
                    self::tx($c),
                    self::usersFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                DeactivateUserUseCaseInterface::class,
                static fn (ContainerInterface $c): DeactivateUserUseCase => new DeactivateUserUseCase(
                    self::service($c, UserRepositoryInterface::class),
                    self::tx($c),
                    self::usersFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                ListUsersHandler::class,
                static fn (ContainerInterface $c): ListUsersHandler
                    => new ListUsersHandler(self::service($c, ListUsersUseCaseInterface::class), self::json($c)),
            )
            ->set(
                GetUserHandler::class,
                static fn (ContainerInterface $c): GetUserHandler
                    => new GetUserHandler(self::service($c, GetUserUseCaseInterface::class), self::json($c)),
            )
            ->set(
                CreateUserHandler::class,
                static fn (ContainerInterface $c): CreateUserHandler
                    => new CreateUserHandler(self::service($c, CreateUserUseCaseInterface::class), self::json($c)),
            )
            ->set(
                UpdateUserHandler::class,
                static fn (ContainerInterface $c): UpdateUserHandler
                    => new UpdateUserHandler(self::service($c, UpdateUserUseCaseInterface::class), self::json($c)),
            )
            ->set(
                DeactivateUserHandler::class,
                static fn (ContainerInterface $c): DeactivateUserHandler
                    => new DeactivateUserHandler(self::service($c, DeactivateUserUseCaseInterface::class), self::json($c)),
            )
            ->set(
                UserNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): UserNotFoundExceptionHandler
                    => new UserNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                UserEmailConflictExceptionHandler::class,
                static fn (ContainerInterface $c): UserEmailConflictExceptionHandler
                    => new UserEmailConflictExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                UserRouteRegistrar::class,
                static fn (ContainerInterface $c): UserRouteRegistrar => new UserRouteRegistrar(
                    self::service($c, ListUsersHandler::class),
                    self::service($c, GetUserHandler::class),
                    self::service($c, CreateUserHandler::class),
                    self::service($c, UpdateUserHandler::class),
                    self::service($c, DeactivateUserHandler::class),
                ),
            );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): UserRepositoryInterface */
    private static function usersFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);
        $clock = self::clock($c);

        return static fn (DatabaseQueryExecutorInterface $exec): UserRepositoryInterface
            => new PdoUserRepository($exec, $orgHolder, $clock);
    }
}
