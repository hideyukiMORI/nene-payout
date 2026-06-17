<?php

declare(strict_types=1);

namespace NenePayout\Organization;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Support\ServiceProviderSupport;
use Psr\Container\ContainerInterface;

final readonly class OrganizationServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                OrganizationRepositoryInterface::class,
                static fn (ContainerInterface $c): OrganizationRepositoryInterface
                    => new PdoOrganizationRepository(self::query($c), self::clock($c)),
            )
            ->set(
                GetOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): GetOrganizationUseCase => new GetOrganizationUseCase(
                    self::service($c, OrganizationRepositoryInterface::class),
                    self::orgHolder($c),
                ),
            )
            ->set(
                UpdateOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateOrganizationUseCase => new UpdateOrganizationUseCase(
                    self::service($c, OrganizationRepositoryInterface::class),
                    self::tx($c),
                    self::organizationsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                GetOrganizationHandler::class,
                static fn (ContainerInterface $c): GetOrganizationHandler => new GetOrganizationHandler(
                    self::service($c, GetOrganizationUseCaseInterface::class),
                    self::json($c),
                ),
            )
            ->set(
                UpdateOrganizationHandler::class,
                static fn (ContainerInterface $c): UpdateOrganizationHandler => new UpdateOrganizationHandler(
                    self::service($c, UpdateOrganizationUseCaseInterface::class),
                    self::json($c),
                ),
            )
            ->set(
                OrganizationNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): OrganizationNotFoundExceptionHandler
                    => new OrganizationNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                OrganizationRouteRegistrar::class,
                static fn (ContainerInterface $c): OrganizationRouteRegistrar => new OrganizationRouteRegistrar(
                    self::service($c, GetOrganizationHandler::class),
                    self::service($c, UpdateOrganizationHandler::class),
                ),
            );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface */
    private static function organizationsFactory(ContainerInterface $c): Closure
    {
        $clock = self::clock($c);

        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationRepositoryInterface
            => new PdoOrganizationRepository($exec, $clock);
    }
}
