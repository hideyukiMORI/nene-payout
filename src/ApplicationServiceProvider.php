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
use NenePayout\Payment\PaymentExecutionNotFoundExceptionHandler;
use NenePayout\Payment\PaymentNotAllowedExceptionHandler;
use NenePayout\Payment\PaymentRouteRegistrar;
use NenePayout\Payment\PaymentServiceProvider;
use NenePayout\ReceivedInvoice\InvoiceNotEditableExceptionHandler;
use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundExceptionHandler;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRouteRegistrar;
use NenePayout\ReceivedInvoice\ReceivedInvoiceServiceProvider;
use NenePayout\User\UserEmailConflictExceptionHandler;
use NenePayout\User\UserNotFoundExceptionHandler;
use NenePayout\User\UserRouteRegistrar;
use NenePayout\User\UserServiceProvider;
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
        $builder->addProvider(new ReceivedInvoiceServiceProvider());
        $builder->addProvider(new PaymentServiceProvider());
        $builder->addProvider(new UserServiceProvider());

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
                    $receivedInvoice = $container->get(ReceivedInvoiceRouteRegistrar::class);
                    $payment = $container->get(PaymentRouteRegistrar::class);
                    $user = $container->get(UserRouteRegistrar::class);

                    if (!$auth instanceof AuthRouteRegistrar) {
                        throw new LogicException('Auth route registrar service is invalid.');
                    }

                    if (!$audit instanceof AuditRouteRegistrar) {
                        throw new LogicException('Audit route registrar service is invalid.');
                    }

                    if (!$vendor instanceof VendorRouteRegistrar) {
                        throw new LogicException('Vendor route registrar service is invalid.');
                    }

                    if (!$receivedInvoice instanceof ReceivedInvoiceRouteRegistrar) {
                        throw new LogicException('Received invoice route registrar service is invalid.');
                    }

                    if (!$payment instanceof PaymentRouteRegistrar) {
                        throw new LogicException('Payment route registrar service is invalid.');
                    }

                    if (!$user instanceof UserRouteRegistrar) {
                        throw new LogicException('User route registrar service is invalid.');
                    }

                    return [$auth, $audit, $vendor, $receivedInvoice, $payment, $user];
                },
            )
            ->set(
                self::EXCEPTION_HANDLERS,
                /** @return list<\Nene2\Error\DomainExceptionHandlerInterface> */
                static function (ContainerInterface $container): array {
                    $invalidCredentials = $container->get(InvalidCredentialsExceptionHandler::class);
                    $vendorNotFound = $container->get(VendorNotFoundExceptionHandler::class);
                    $invoiceNotFound = $container->get(ReceivedInvoiceNotFoundExceptionHandler::class);
                    $invoiceNotEditable = $container->get(InvoiceNotEditableExceptionHandler::class);
                    $paymentNotFound = $container->get(PaymentExecutionNotFoundExceptionHandler::class);
                    $paymentNotAllowed = $container->get(PaymentNotAllowedExceptionHandler::class);
                    $userNotFound = $container->get(UserNotFoundExceptionHandler::class);
                    $userEmailConflict = $container->get(UserEmailConflictExceptionHandler::class);

                    if (!$invalidCredentials instanceof InvalidCredentialsExceptionHandler) {
                        throw new LogicException('Invalid credentials exception handler service is invalid.');
                    }

                    if (!$vendorNotFound instanceof VendorNotFoundExceptionHandler) {
                        throw new LogicException('Vendor not found exception handler service is invalid.');
                    }

                    if (!$invoiceNotFound instanceof ReceivedInvoiceNotFoundExceptionHandler) {
                        throw new LogicException('Received invoice not found exception handler service is invalid.');
                    }

                    if (!$invoiceNotEditable instanceof InvoiceNotEditableExceptionHandler) {
                        throw new LogicException('Invoice not editable exception handler service is invalid.');
                    }

                    if (!$paymentNotFound instanceof PaymentExecutionNotFoundExceptionHandler) {
                        throw new LogicException('Payment execution not found exception handler service is invalid.');
                    }

                    if (!$paymentNotAllowed instanceof PaymentNotAllowedExceptionHandler) {
                        throw new LogicException('Payment not allowed exception handler service is invalid.');
                    }

                    if (!$userNotFound instanceof UserNotFoundExceptionHandler) {
                        throw new LogicException('User not found exception handler service is invalid.');
                    }

                    if (!$userEmailConflict instanceof UserEmailConflictExceptionHandler) {
                        throw new LogicException('User email conflict exception handler service is invalid.');
                    }

                    return [$invalidCredentials, $vendorNotFound, $invoiceNotFound, $invoiceNotEditable, $paymentNotFound, $paymentNotAllowed, $userNotFound, $userEmailConflict];
                },
            );
    }
}
