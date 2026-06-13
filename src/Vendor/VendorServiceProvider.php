<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Support\ServiceProviderSupport;
use Psr\Container\ContainerInterface;

final readonly class VendorServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                VendorRepositoryInterface::class,
                static fn (ContainerInterface $c): VendorRepositoryInterface
                    => new PdoVendorRepository(self::query($c), self::orgHolder($c), self::clock($c)),
            )
            ->set(
                ListVendorsUseCaseInterface::class,
                static fn (ContainerInterface $c): ListVendorsUseCase
                    => new ListVendorsUseCase(self::service($c, VendorRepositoryInterface::class)),
            )
            ->set(
                GetVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): GetVendorUseCase
                    => new GetVendorUseCase(self::service($c, VendorRepositoryInterface::class)),
            )
            ->set(
                CreateVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateVendorUseCase => new CreateVendorUseCase(
                    self::tx($c),
                    self::vendorsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                UpdateVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateVendorUseCase => new UpdateVendorUseCase(
                    self::service($c, VendorRepositoryInterface::class),
                    self::tx($c),
                    self::vendorsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                DeactivateVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): DeactivateVendorUseCase => new DeactivateVendorUseCase(
                    self::service($c, VendorRepositoryInterface::class),
                    self::tx($c),
                    self::vendorsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                ListVendorsHandler::class,
                static fn (ContainerInterface $c): ListVendorsHandler
                    => new ListVendorsHandler(self::service($c, ListVendorsUseCaseInterface::class), self::json($c)),
            )
            ->set(
                GetVendorHandler::class,
                static fn (ContainerInterface $c): GetVendorHandler
                    => new GetVendorHandler(self::service($c, GetVendorUseCaseInterface::class), self::json($c)),
            )
            ->set(
                CreateVendorHandler::class,
                static fn (ContainerInterface $c): CreateVendorHandler
                    => new CreateVendorHandler(self::service($c, CreateVendorUseCaseInterface::class), self::json($c)),
            )
            ->set(
                UpdateVendorHandler::class,
                static fn (ContainerInterface $c): UpdateVendorHandler
                    => new UpdateVendorHandler(self::service($c, UpdateVendorUseCaseInterface::class), self::json($c)),
            )
            ->set(
                DeactivateVendorHandler::class,
                static fn (ContainerInterface $c): DeactivateVendorHandler
                    => new DeactivateVendorHandler(self::service($c, DeactivateVendorUseCaseInterface::class), self::json($c)),
            )
            ->set(
                VendorNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): VendorNotFoundExceptionHandler
                    => new VendorNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                VendorRouteRegistrar::class,
                static fn (ContainerInterface $c): VendorRouteRegistrar => new VendorRouteRegistrar(
                    self::service($c, ListVendorsHandler::class),
                    self::service($c, GetVendorHandler::class),
                    self::service($c, CreateVendorHandler::class),
                    self::service($c, UpdateVendorHandler::class),
                    self::service($c, DeactivateVendorHandler::class),
                ),
            );
    }

    /** @return Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface */
    private static function vendorsFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);
        $clock = self::clock($c);

        return static fn (DatabaseQueryExecutorInterface $exec): VendorRepositoryInterface
            => new PdoVendorRepository($exec, $orgHolder, $clock);
    }
}
