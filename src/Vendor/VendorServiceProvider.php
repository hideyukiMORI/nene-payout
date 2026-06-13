<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\ApplicationServiceProvider;
use NenePayout\Audit\AuditServiceProvider;
use Psr\Container\ContainerInterface;

final readonly class VendorServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                VendorRepositoryInterface::class,
                static fn (ContainerInterface $c): VendorRepositoryInterface
                    => new PdoVendorRepository(self::query($c), self::orgHolder($c)),
            )
            ->set(
                ListVendorsUseCaseInterface::class,
                static fn (ContainerInterface $c): ListVendorsUseCase => new ListVendorsUseCase(self::repository($c)),
            )
            ->set(
                GetVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): GetVendorUseCase => new GetVendorUseCase(self::repository($c)),
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
                    self::repository($c),
                    self::tx($c),
                    self::vendorsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                DeactivateVendorUseCaseInterface::class,
                static fn (ContainerInterface $c): DeactivateVendorUseCase => new DeactivateVendorUseCase(
                    self::repository($c),
                    self::tx($c),
                    self::vendorsFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                ListVendorsHandler::class,
                static fn (ContainerInterface $c): ListVendorsHandler => new ListVendorsHandler(
                    self::listUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                GetVendorHandler::class,
                static fn (ContainerInterface $c): GetVendorHandler => new GetVendorHandler(
                    self::getUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                CreateVendorHandler::class,
                static fn (ContainerInterface $c): CreateVendorHandler => new CreateVendorHandler(
                    self::createUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                UpdateVendorHandler::class,
                static fn (ContainerInterface $c): UpdateVendorHandler => new UpdateVendorHandler(
                    self::updateUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                DeactivateVendorHandler::class,
                static fn (ContainerInterface $c): DeactivateVendorHandler => new DeactivateVendorHandler(
                    self::deactivateUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                VendorNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): VendorNotFoundExceptionHandler
                    => new VendorNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                VendorRouteRegistrar::class,
                static fn (ContainerInterface $c): VendorRouteRegistrar => new VendorRouteRegistrar(
                    self::handler($c, ListVendorsHandler::class),
                    self::handler($c, GetVendorHandler::class),
                    self::handler($c, CreateVendorHandler::class),
                    self::handler($c, UpdateVendorHandler::class),
                    self::handler($c, DeactivateVendorHandler::class),
                ),
            );
    }

    private static function query(ContainerInterface $c): DatabaseQueryExecutorInterface
    {
        $query = $c->get(DatabaseQueryExecutorInterface::class);

        if (!$query instanceof DatabaseQueryExecutorInterface) {
            throw new LogicException('Database query executor service is invalid.');
        }

        return $query;
    }

    /** @return RequestScopedHolder<string> */
    private static function orgHolder(ContainerInterface $c): RequestScopedHolder
    {
        $holder = $c->get(ApplicationServiceProvider::ORG_ID_HOLDER);

        if (!$holder instanceof RequestScopedHolder) {
            throw new LogicException('Org id holder service is invalid.');
        }

        /** @var RequestScopedHolder<string> $holder */
        return $holder;
    }

    private static function tx(ContainerInterface $c): DatabaseTransactionManagerInterface
    {
        $tx = $c->get(DatabaseTransactionManagerInterface::class);

        if (!$tx instanceof DatabaseTransactionManagerInterface) {
            throw new LogicException('Transaction manager service is invalid.');
        }

        return $tx;
    }

    private static function json(ContainerInterface $c): JsonResponseFactory
    {
        $json = $c->get(JsonResponseFactory::class);

        if (!$json instanceof JsonResponseFactory) {
            throw new LogicException('JSON response factory service is invalid.');
        }

        return $json;
    }

    private static function problemDetails(ContainerInterface $c): ProblemDetailsResponseFactory
    {
        $pd = $c->get(ProblemDetailsResponseFactory::class);

        if (!$pd instanceof ProblemDetailsResponseFactory) {
            throw new LogicException('Problem details factory service is invalid.');
        }

        return $pd;
    }

    private static function repository(ContainerInterface $c): VendorRepositoryInterface
    {
        $repo = $c->get(VendorRepositoryInterface::class);

        if (!$repo instanceof VendorRepositoryInterface) {
            throw new LogicException('Vendor repository service is invalid.');
        }

        return $repo;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface */
    private static function vendorsFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);

        return static fn (DatabaseQueryExecutorInterface $exec): VendorRepositoryInterface
            => new PdoVendorRepository($exec, $orgHolder);
    }

    private static function listUseCase(ContainerInterface $c): ListVendorsUseCaseInterface
    {
        $u = $c->get(ListVendorsUseCaseInterface::class);

        if (!$u instanceof ListVendorsUseCaseInterface) {
            throw new LogicException('List vendors use case service is invalid.');
        }

        return $u;
    }

    private static function getUseCase(ContainerInterface $c): GetVendorUseCaseInterface
    {
        $u = $c->get(GetVendorUseCaseInterface::class);

        if (!$u instanceof GetVendorUseCaseInterface) {
            throw new LogicException('Get vendor use case service is invalid.');
        }

        return $u;
    }

    private static function createUseCase(ContainerInterface $c): CreateVendorUseCaseInterface
    {
        $u = $c->get(CreateVendorUseCaseInterface::class);

        if (!$u instanceof CreateVendorUseCaseInterface) {
            throw new LogicException('Create vendor use case service is invalid.');
        }

        return $u;
    }

    private static function updateUseCase(ContainerInterface $c): UpdateVendorUseCaseInterface
    {
        $u = $c->get(UpdateVendorUseCaseInterface::class);

        if (!$u instanceof UpdateVendorUseCaseInterface) {
            throw new LogicException('Update vendor use case service is invalid.');
        }

        return $u;
    }

    private static function deactivateUseCase(ContainerInterface $c): DeactivateVendorUseCaseInterface
    {
        $u = $c->get(DeactivateVendorUseCaseInterface::class);

        if (!$u instanceof DeactivateVendorUseCaseInterface) {
            throw new LogicException('Deactivate vendor use case service is invalid.');
        }

        return $u;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private static function handler(ContainerInterface $c, string $class): object
    {
        $handler = $c->get($class);

        if (!$handler instanceof $class) {
            throw new LogicException($class . ' service is invalid.');
        }

        return $handler;
    }
}
