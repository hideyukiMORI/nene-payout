<?php

declare(strict_types=1);

namespace NenePayout;

use LogicException;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRouteRegistrar;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Auth\AuthRouteRegistrar;
use NenePayout\Auth\AuthServiceProvider;
use NenePayout\Auth\InvalidCredentialsExceptionHandler;
use NenePayout\Organization\OrganizationServiceProvider;
use NenePayout\Vendor\VendorNotFoundExceptionHandler;
use NenePayout\Vendor\VendorRouteRegistrar;
use NenePayout\Vendor\VendorServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Registers NeNe Payout application services and exposes the aggregate
 * string-keyed services that the runtime wires into the HTTP pipeline:
 * the shared tenant org-id holder, route registrars, and domain exception
 * handlers.
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

    /** Container key for the shared RequestScopedHolder<string> carrying organization_id. */
    public const ORG_ID_HOLDER = 'nene_payout.org_id_holder';

    public function register(ContainerBuilder $builder): void
    {
        $builder->addProvider(new OrganizationServiceProvider());
        $builder->addProvider(new AuthServiceProvider());
        $builder->addProvider(new AuditServiceProvider());
        $builder->addProvider(new VendorServiceProvider());

        $builder
            ->set(
                self::ORG_ID_HOLDER,
                /** @return RequestScopedHolder<string> */
                static function (ContainerInterface $container): RequestScopedHolder {
                    /** @var RequestScopedHolder<string> */
                    return new RequestScopedHolder();
                },
            )
            ->set(
                self::ROUTE_REGISTRARS,
                /** @return list<callable(\Nene2\Routing\Router): void> */
                static function (ContainerInterface $container): array {
                    $auth = $container->get(AuthRouteRegistrar::class);
                    $audit = $container->get(AuditRouteRegistrar::class);
                    $vendor = $container->get(VendorRouteRegistrar::class);

                    if (!$auth instanceof AuthRouteRegistrar) {
                        throw new LogicException('Auth route registrar service is invalid.');
                    }

                    if (!$audit instanceof AuditRouteRegistrar) {
                        throw new LogicException('Audit route registrar service is invalid.');
                    }

                    if (!$vendor instanceof VendorRouteRegistrar) {
                        throw new LogicException('Vendor route registrar service is invalid.');
                    }

                    return [$auth, $audit, $vendor];
                },
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                /** @return list<\Nene2\Error\DomainExceptionHandlerInterface> */
                static function (ContainerInterface $container): array {
                    $invalidCredentials = $container->get(InvalidCredentialsExceptionHandler::class);
                    $vendorNotFound = $container->get(VendorNotFoundExceptionHandler::class);

                    if (!$invalidCredentials instanceof InvalidCredentialsExceptionHandler) {
                        throw new LogicException('Invalid credentials exception handler service is invalid.');
                    }

                    if (!$vendorNotFound instanceof VendorNotFoundExceptionHandler) {
                        throw new LogicException('Vendor not found exception handler service is invalid.');
                    }

                    return [$invalidCredentials, $vendorNotFound];
                },
            );
    }
}
