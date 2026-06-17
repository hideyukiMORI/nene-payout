<?php

declare(strict_types=1);

namespace NenePayout\Organization\Management;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\PdoOrganizationRepository;
use NenePayout\Support\ServiceProviderSupport;
use Psr\Container\ContainerInterface;

/**
 * Wires the superadmin cross-tenant organization management slice
 * (/api/v1/organizations). Reuses the shared OrganizationRepositoryInterface.
 */
final readonly class OrganizationManagementServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                ListOrganizationsUseCaseInterface::class,
                static fn (ContainerInterface $c): ListOrganizationsUseCase
                    => new ListOrganizationsUseCase(self::service($c, OrganizationRepositoryInterface::class)),
            )
            ->set(
                GetOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): GetOrganizationUseCase
                    => new GetOrganizationUseCase(self::service($c, OrganizationRepositoryInterface::class)),
            )
            ->set(
                CreateOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateOrganizationUseCase => new CreateOrganizationUseCase(
                    self::tx($c),
                    self::organizationsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                ),
            )
            ->set(
                UpdateOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateOrganizationUseCase => new UpdateOrganizationUseCase(
                    self::service($c, OrganizationRepositoryInterface::class),
                    self::tx($c),
                    self::organizationsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                ),
            )
            ->set(
                DeactivateOrganizationUseCaseInterface::class,
                static fn (ContainerInterface $c): DeactivateOrganizationUseCase => new DeactivateOrganizationUseCase(
                    self::service($c, OrganizationRepositoryInterface::class),
                    self::tx($c),
                    self::organizationsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                ),
            )
            ->set(
                ListOrganizationsHandler::class,
                static fn (ContainerInterface $c): ListOrganizationsHandler => new ListOrganizationsHandler(
                    self::service($c, ListOrganizationsUseCaseInterface::class),
                    self::json($c),
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
                CreateOrganizationHandler::class,
                static fn (ContainerInterface $c): CreateOrganizationHandler => new CreateOrganizationHandler(
                    self::service($c, CreateOrganizationUseCaseInterface::class),
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
                DeactivateOrganizationHandler::class,
                static fn (ContainerInterface $c): DeactivateOrganizationHandler => new DeactivateOrganizationHandler(
                    self::service($c, DeactivateOrganizationUseCaseInterface::class),
                    self::json($c),
                ),
            )
            ->set(
                OrganizationSlugConflictExceptionHandler::class,
                static fn (ContainerInterface $c): OrganizationSlugConflictExceptionHandler
                    => new OrganizationSlugConflictExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                OrganizationsRouteRegistrar::class,
                static fn (ContainerInterface $c): OrganizationsRouteRegistrar => new OrganizationsRouteRegistrar(
                    self::service($c, ListOrganizationsHandler::class),
                    self::service($c, GetOrganizationHandler::class),
                    self::service($c, CreateOrganizationHandler::class),
                    self::service($c, UpdateOrganizationHandler::class),
                    self::service($c, DeactivateOrganizationHandler::class),
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
