<?php

declare(strict_types=1);

namespace NenePayout;

use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * Registers NeNe Payout application services and exposes the aggregate
 * string-keyed services that the runtime wires into the HTTP pipeline:
 * route registrars and domain exception handlers.
 *
 * Domain service providers (Vendor, ReceivedInvoice, Payment, …) are added here
 * as they land in later slices; their route registrars and exception handlers
 * are collected into the lists below.
 */
final readonly class ApplicationServiceProvider implements ServiceProviderInterface
{
    /** Container key for the list of application route registrar callables. */
    public const ROUTE_REGISTRARS = 'nene_payout.route_registrars';

    /** Container key for the list of application domain exception handlers. */
    public const EXCEPTION_HANDLERS = 'nene_payout.exception_handlers';

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                self::ROUTE_REGISTRARS,
                /** @return list<callable(\Nene2\Routing\Router): void> */
                static fn (ContainerInterface $container): array => [],
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                /** @return list<\Nene2\Error\DomainExceptionHandlerInterface> */
                static fn (ContainerInterface $container): array => [],
            );
    }
}
