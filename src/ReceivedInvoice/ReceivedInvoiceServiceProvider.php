<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

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
use NenePayout\Vendor\VendorRepositoryInterface;
use Psr\Container\ContainerInterface;

final readonly class ReceivedInvoiceServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                ReceivedInvoiceRepositoryInterface::class,
                static fn (ContainerInterface $c): ReceivedInvoiceRepositoryInterface
                    => new PdoReceivedInvoiceRepository(self::query($c), self::orgHolder($c)),
            )
            ->set(
                ListReceivedInvoicesUseCaseInterface::class,
                static fn (ContainerInterface $c): ListReceivedInvoicesUseCase => new ListReceivedInvoicesUseCase(self::repository($c)),
            )
            ->set(
                GetReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): GetReceivedInvoiceUseCase => new GetReceivedInvoiceUseCase(self::repository($c)),
            )
            ->set(
                CreateReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateReceivedInvoiceUseCase => new CreateReceivedInvoiceUseCase(
                    self::vendorRepository($c),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                UpdateReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateReceivedInvoiceUseCase => new UpdateReceivedInvoiceUseCase(
                    self::repository($c),
                    self::vendorRepository($c),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                VoidReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): VoidReceivedInvoiceUseCase => new VoidReceivedInvoiceUseCase(
                    self::repository($c),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                ListReceivedInvoicesHandler::class,
                static fn (ContainerInterface $c): ListReceivedInvoicesHandler => new ListReceivedInvoicesHandler(
                    self::listUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                GetReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): GetReceivedInvoiceHandler => new GetReceivedInvoiceHandler(
                    self::getUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                CreateReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): CreateReceivedInvoiceHandler => new CreateReceivedInvoiceHandler(
                    self::createUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                UpdateReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): UpdateReceivedInvoiceHandler => new UpdateReceivedInvoiceHandler(
                    self::updateUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                VoidReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): VoidReceivedInvoiceHandler => new VoidReceivedInvoiceHandler(
                    self::voidUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                ReceivedInvoiceNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): ReceivedInvoiceNotFoundExceptionHandler
                    => new ReceivedInvoiceNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                InvoiceNotEditableExceptionHandler::class,
                static fn (ContainerInterface $c): InvoiceNotEditableExceptionHandler
                    => new InvoiceNotEditableExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                ReceivedInvoiceRouteRegistrar::class,
                static fn (ContainerInterface $c): ReceivedInvoiceRouteRegistrar => new ReceivedInvoiceRouteRegistrar(
                    self::handler($c, ListReceivedInvoicesHandler::class),
                    self::handler($c, GetReceivedInvoiceHandler::class),
                    self::handler($c, CreateReceivedInvoiceHandler::class),
                    self::handler($c, UpdateReceivedInvoiceHandler::class),
                    self::handler($c, VoidReceivedInvoiceHandler::class),
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

    private static function repository(ContainerInterface $c): ReceivedInvoiceRepositoryInterface
    {
        $repo = $c->get(ReceivedInvoiceRepositoryInterface::class);

        if (!$repo instanceof ReceivedInvoiceRepositoryInterface) {
            throw new LogicException('Received invoice repository service is invalid.');
        }

        return $repo;
    }

    private static function vendorRepository(ContainerInterface $c): VendorRepositoryInterface
    {
        $repo = $c->get(VendorRepositoryInterface::class);

        if (!$repo instanceof VendorRepositoryInterface) {
            throw new LogicException('Vendor repository service is invalid.');
        }

        return $repo;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface */
    private static function invoicesFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);

        return static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface
            => new PdoReceivedInvoiceRepository($exec, $orgHolder);
    }

    private static function listUseCase(ContainerInterface $c): ListReceivedInvoicesUseCaseInterface
    {
        $u = $c->get(ListReceivedInvoicesUseCaseInterface::class);

        if (!$u instanceof ListReceivedInvoicesUseCaseInterface) {
            throw new LogicException('List received invoices use case service is invalid.');
        }

        return $u;
    }

    private static function getUseCase(ContainerInterface $c): GetReceivedInvoiceUseCaseInterface
    {
        $u = $c->get(GetReceivedInvoiceUseCaseInterface::class);

        if (!$u instanceof GetReceivedInvoiceUseCaseInterface) {
            throw new LogicException('Get received invoice use case service is invalid.');
        }

        return $u;
    }

    private static function createUseCase(ContainerInterface $c): CreateReceivedInvoiceUseCaseInterface
    {
        $u = $c->get(CreateReceivedInvoiceUseCaseInterface::class);

        if (!$u instanceof CreateReceivedInvoiceUseCaseInterface) {
            throw new LogicException('Create received invoice use case service is invalid.');
        }

        return $u;
    }

    private static function updateUseCase(ContainerInterface $c): UpdateReceivedInvoiceUseCaseInterface
    {
        $u = $c->get(UpdateReceivedInvoiceUseCaseInterface::class);

        if (!$u instanceof UpdateReceivedInvoiceUseCaseInterface) {
            throw new LogicException('Update received invoice use case service is invalid.');
        }

        return $u;
    }

    private static function voidUseCase(ContainerInterface $c): VoidReceivedInvoiceUseCaseInterface
    {
        $u = $c->get(VoidReceivedInvoiceUseCaseInterface::class);

        if (!$u instanceof VoidReceivedInvoiceUseCaseInterface) {
            throw new LogicException('Void received invoice use case service is invalid.');
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
