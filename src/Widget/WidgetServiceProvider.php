<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Auth\TokenIssuerInterface;
use Nene2\Auth\TokenVerifierInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Payment\GetPaymentExecutionHandler;
use NenePayout\Payment\InitiatePaymentHandler;
use NenePayout\Payment\InitiatePaymentUseCaseInterface;
use NenePayout\Payment\ListPaymentExecutionsHandler;
use NenePayout\ReceivedInvoice\AttachReceivedInvoicePdfHandler;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceUseCaseInterface;
use NenePayout\ReceivedInvoice\GetReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\ListReceivedInvoicesHandler;
use NenePayout\ReceivedInvoice\UpdateReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\VoidReceivedInvoiceHandler;
use NenePayout\Support\ServiceProviderSupport;
use NenePayout\Vendor\CreateVendorHandler;
use NenePayout\Vendor\CreateVendorUseCaseInterface;
use NenePayout\Vendor\DeactivateVendorHandler;
use NenePayout\Vendor\GetVendorHandler;
use NenePayout\Vendor\ListVendorsHandler;
use NenePayout\Vendor\UpdateVendorHandler;
use Psr\Container\ContainerInterface;

/**
 * Wires the embeddable widget (ADR 0021): the org-scoped token service, the
 * `/api/v1/widget/*` auth middleware, the new context / generation / Mode-A
 * handlers, and the route registrar that reuses the existing admin handlers for
 * the Mode-B management surface.
 */
final readonly class WidgetServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    /** Default widget-token lifetime: 90 days (override with WIDGET_TOKEN_TTL_SECONDS). */
    private const DEFAULT_TTL_SECONDS = 7776000;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                WidgetTokenService::class,
                static function (ContainerInterface $c): WidgetTokenService {
                    $ttlEnv = getenv('WIDGET_TOKEN_TTL_SECONDS');
                    $ttl = is_string($ttlEnv) && ctype_digit($ttlEnv) && (int) $ttlEnv > 0
                        ? (int) $ttlEnv
                        : self::DEFAULT_TTL_SECONDS;

                    return new WidgetTokenService(
                        self::service($c, TokenIssuerInterface::class),
                        self::service($c, TokenVerifierInterface::class),
                        self::clock($c),
                        $ttl,
                    );
                },
            )
            ->set(
                WidgetAuthMiddleware::class,
                static fn (ContainerInterface $c): WidgetAuthMiddleware => new WidgetAuthMiddleware(
                    self::service($c, WidgetTokenService::class),
                    self::orgHolder($c),
                    self::problemDetails($c),
                ),
            )
            ->set(
                WidgetTokenExceptionHandler::class,
                static fn (ContainerInterface $c): WidgetTokenExceptionHandler
                    => new WidgetTokenExceptionHandler(self::problemDetails($c)),
            )
            ->set(
                GetWidgetContextHandler::class,
                static fn (ContainerInterface $c): GetWidgetContextHandler => new GetWidgetContextHandler(
                    self::service($c, OrganizationRepositoryInterface::class),
                    self::orgHolder($c),
                    self::json($c),
                ),
            )
            ->set(
                GenerateWidgetTokenHandler::class,
                static fn (ContainerInterface $c): GenerateWidgetTokenHandler => new GenerateWidgetTokenHandler(
                    self::service($c, WidgetTokenService::class),
                    self::json($c),
                ),
            )
            ->set(
                InitiateWidgetQuickPaymentHandler::class,
                static fn (ContainerInterface $c): InitiateWidgetQuickPaymentHandler => new InitiateWidgetQuickPaymentHandler(
                    self::service($c, CreateVendorUseCaseInterface::class),
                    self::service($c, CreateReceivedInvoiceUseCaseInterface::class),
                    self::service($c, InitiatePaymentUseCaseInterface::class),
                    self::json($c),
                ),
            )
            ->set(
                WidgetRouteRegistrar::class,
                static fn (ContainerInterface $c): WidgetRouteRegistrar => new WidgetRouteRegistrar(
                    self::service($c, GenerateWidgetTokenHandler::class),
                    self::service($c, GetWidgetContextHandler::class),
                    self::service($c, InitiateWidgetQuickPaymentHandler::class),
                    self::service($c, ListReceivedInvoicesHandler::class),
                    self::service($c, GetReceivedInvoiceHandler::class),
                    self::service($c, CreateReceivedInvoiceHandler::class),
                    self::service($c, UpdateReceivedInvoiceHandler::class),
                    self::service($c, VoidReceivedInvoiceHandler::class),
                    self::service($c, AttachReceivedInvoicePdfHandler::class),
                    self::service($c, InitiatePaymentHandler::class),
                    self::service($c, ListVendorsHandler::class),
                    self::service($c, GetVendorHandler::class),
                    self::service($c, CreateVendorHandler::class),
                    self::service($c, UpdateVendorHandler::class),
                    self::service($c, DeactivateVendorHandler::class),
                    self::service($c, ListPaymentExecutionsHandler::class),
                    self::service($c, GetPaymentExecutionHandler::class),
                ),
            );
    }
}
