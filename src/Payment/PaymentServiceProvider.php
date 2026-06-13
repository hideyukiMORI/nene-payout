<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\ApplicationServiceProvider;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Payment\Gateway\PaymentGatewayInterface;
use NenePayout\Payment\Gateway\StubGatewayAdapter;
use NenePayout\ReceivedInvoice\PdoReceivedInvoiceRepository;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use Psr\Container\ContainerInterface;

final readonly class PaymentServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                PaymentExecutionRepositoryInterface::class,
                static fn (ContainerInterface $c): PaymentExecutionRepositoryInterface
                    => new PdoPaymentExecutionRepository(self::query($c), self::orgHolder($c)),
            )
            ->set(
                PaymentGatewayInterface::class,
                // Placeholder until the Stripe adapter + gateway-settings land (Issue follow-up).
                static fn (ContainerInterface $c): PaymentGatewayInterface => new StubGatewayAdapter(),
            )
            ->set(
                ListPaymentExecutionsUseCaseInterface::class,
                static fn (ContainerInterface $c): ListPaymentExecutionsUseCase => new ListPaymentExecutionsUseCase(self::repository($c)),
            )
            ->set(
                GetPaymentExecutionUseCaseInterface::class,
                static fn (ContainerInterface $c): GetPaymentExecutionUseCase => new GetPaymentExecutionUseCase(self::repository($c)),
            )
            ->set(
                InitiatePaymentUseCaseInterface::class,
                static fn (ContainerInterface $c): InitiatePaymentUseCase => new InitiatePaymentUseCase(
                    self::invoiceRepository($c),
                    self::gateway($c),
                    self::tx($c),
                    self::paymentsFactory($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                    self::clock($c),
                ),
            )
            ->set(
                InitiatePaymentHandler::class,
                static fn (ContainerInterface $c): InitiatePaymentHandler => new InitiatePaymentHandler(
                    self::initiateUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                ListPaymentExecutionsHandler::class,
                static fn (ContainerInterface $c): ListPaymentExecutionsHandler => new ListPaymentExecutionsHandler(
                    self::listUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                GetPaymentExecutionHandler::class,
                static fn (ContainerInterface $c): GetPaymentExecutionHandler => new GetPaymentExecutionHandler(
                    self::getUseCase($c),
                    self::json($c),
                ),
            )
            ->set(
                PaymentExecutionNotFoundExceptionHandler::class,
                static fn (ContainerInterface $c): PaymentExecutionNotFoundExceptionHandler
                    => new PaymentExecutionNotFoundExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                PaymentNotAllowedExceptionHandler::class,
                static fn (ContainerInterface $c): PaymentNotAllowedExceptionHandler
                    => new PaymentNotAllowedExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                PaymentRouteRegistrar::class,
                static fn (ContainerInterface $c): PaymentRouteRegistrar => new PaymentRouteRegistrar(
                    self::handler($c, InitiatePaymentHandler::class),
                    self::handler($c, ListPaymentExecutionsHandler::class),
                    self::handler($c, GetPaymentExecutionHandler::class),
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

    private static function clock(ContainerInterface $c): ClockInterface
    {
        $clock = $c->get(ClockInterface::class);

        if (!$clock instanceof ClockInterface) {
            throw new LogicException('Clock service is invalid.');
        }

        return $clock;
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

    private static function repository(ContainerInterface $c): PaymentExecutionRepositoryInterface
    {
        $repo = $c->get(PaymentExecutionRepositoryInterface::class);

        if (!$repo instanceof PaymentExecutionRepositoryInterface) {
            throw new LogicException('Payment execution repository service is invalid.');
        }

        return $repo;
    }

    private static function invoiceRepository(ContainerInterface $c): ReceivedInvoiceRepositoryInterface
    {
        $repo = $c->get(ReceivedInvoiceRepositoryInterface::class);

        if (!$repo instanceof ReceivedInvoiceRepositoryInterface) {
            throw new LogicException('Received invoice repository service is invalid.');
        }

        return $repo;
    }

    private static function gateway(ContainerInterface $c): PaymentGatewayInterface
    {
        $gateway = $c->get(PaymentGatewayInterface::class);

        if (!$gateway instanceof PaymentGatewayInterface) {
            throw new LogicException('Payment gateway service is invalid.');
        }

        return $gateway;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): PaymentExecutionRepositoryInterface */
    private static function paymentsFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);

        return static fn (DatabaseQueryExecutorInterface $exec): PaymentExecutionRepositoryInterface
            => new PdoPaymentExecutionRepository($exec, $orgHolder);
    }

    /** @return Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface */
    private static function invoicesFactory(ContainerInterface $c): Closure
    {
        $orgHolder = self::orgHolder($c);

        return static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface
            => new PdoReceivedInvoiceRepository($exec, $orgHolder);
    }

    private static function initiateUseCase(ContainerInterface $c): InitiatePaymentUseCaseInterface
    {
        $u = $c->get(InitiatePaymentUseCaseInterface::class);

        if (!$u instanceof InitiatePaymentUseCaseInterface) {
            throw new LogicException('Initiate payment use case service is invalid.');
        }

        return $u;
    }

    private static function listUseCase(ContainerInterface $c): ListPaymentExecutionsUseCaseInterface
    {
        $u = $c->get(ListPaymentExecutionsUseCaseInterface::class);

        if (!$u instanceof ListPaymentExecutionsUseCaseInterface) {
            throw new LogicException('List payment executions use case service is invalid.');
        }

        return $u;
    }

    private static function getUseCase(ContainerInterface $c): GetPaymentExecutionUseCaseInterface
    {
        $u = $c->get(GetPaymentExecutionUseCaseInterface::class);

        if (!$u instanceof GetPaymentExecutionUseCaseInterface) {
            throw new LogicException('Get payment execution use case service is invalid.');
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
