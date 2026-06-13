<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Payment\Gateway\PaymentGatewayInterface;
use NenePayout\Payment\Gateway\StubGatewayAdapter;
use NenePayout\ReceivedInvoice\PdoReceivedInvoiceRepository;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\Support\ServiceProviderSupport;
use Psr\Container\ContainerInterface;

final readonly class PaymentServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                PaymentExecutionRepositoryInterface::class,
                static fn (ContainerInterface $c): PaymentExecutionRepositoryInterface
                    => new PdoPaymentExecutionRepository(self::query($c), self::orgHolder($c)),
            )
            ->set(
                // Placeholder until the Stripe adapter + gateway-settings land.
                PaymentGatewayInterface::class,
                static fn (ContainerInterface $c): PaymentGatewayInterface => new StubGatewayAdapter(),
            )
            ->set(
                ListPaymentExecutionsUseCaseInterface::class,
                static fn (ContainerInterface $c): ListPaymentExecutionsUseCase
                    => new ListPaymentExecutionsUseCase(self::service($c, PaymentExecutionRepositoryInterface::class)),
            )
            ->set(
                GetPaymentExecutionUseCaseInterface::class,
                static fn (ContainerInterface $c): GetPaymentExecutionUseCase
                    => new GetPaymentExecutionUseCase(self::service($c, PaymentExecutionRepositoryInterface::class)),
            )
            ->set(
                InitiatePaymentUseCaseInterface::class,
                static fn (ContainerInterface $c): InitiatePaymentUseCase => new InitiatePaymentUseCase(
                    self::service($c, ReceivedInvoiceRepositoryInterface::class),
                    self::service($c, PaymentGatewayInterface::class),
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
                static fn (ContainerInterface $c): InitiatePaymentHandler
                    => new InitiatePaymentHandler(self::service($c, InitiatePaymentUseCaseInterface::class), self::json($c)),
            )
            ->set(
                ListPaymentExecutionsHandler::class,
                static fn (ContainerInterface $c): ListPaymentExecutionsHandler
                    => new ListPaymentExecutionsHandler(self::service($c, ListPaymentExecutionsUseCaseInterface::class), self::json($c)),
            )
            ->set(
                GetPaymentExecutionHandler::class,
                static fn (ContainerInterface $c): GetPaymentExecutionHandler
                    => new GetPaymentExecutionHandler(self::service($c, GetPaymentExecutionUseCaseInterface::class), self::json($c)),
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
                    self::service($c, InitiatePaymentHandler::class),
                    self::service($c, ListPaymentExecutionsHandler::class),
                    self::service($c, GetPaymentExecutionHandler::class),
                ),
            );
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
        $clock = self::clock($c);

        return static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface
            => new PdoReceivedInvoiceRepository($exec, $orgHolder, $clock);
    }
}
