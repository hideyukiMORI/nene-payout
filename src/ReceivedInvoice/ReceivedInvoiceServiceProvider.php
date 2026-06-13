<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Closure;
use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use NenePayout\Audit\AuditServiceProvider;
use NenePayout\Http\RuntimeServiceProvider;
use NenePayout\ReceivedInvoice\Pdf\LocalPdfStorage;
use NenePayout\ReceivedInvoice\Pdf\PdfStorageInterface;
use NenePayout\Support\ServiceProviderSupport;
use NenePayout\Vendor\VendorRepositoryInterface;
use Psr\Container\ContainerInterface;

final readonly class ReceivedInvoiceServiceProvider implements ServiceProviderInterface
{
    use ServiceProviderSupport;

    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                ReceivedInvoiceRepositoryInterface::class,
                static fn (ContainerInterface $c): ReceivedInvoiceRepositoryInterface
                    => new PdoReceivedInvoiceRepository(self::query($c), self::orgHolder($c), self::clock($c)),
            )
            ->set(
                ListReceivedInvoicesUseCaseInterface::class,
                static fn (ContainerInterface $c): ListReceivedInvoicesUseCase
                    => new ListReceivedInvoicesUseCase(self::service($c, ReceivedInvoiceRepositoryInterface::class)),
            )
            ->set(
                GetReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): GetReceivedInvoiceUseCase
                    => new GetReceivedInvoiceUseCase(self::service($c, ReceivedInvoiceRepositoryInterface::class)),
            )
            ->set(
                CreateReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): CreateReceivedInvoiceUseCase => new CreateReceivedInvoiceUseCase(
                    self::service($c, VendorRepositoryInterface::class),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                UpdateReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): UpdateReceivedInvoiceUseCase => new UpdateReceivedInvoiceUseCase(
                    self::service($c, ReceivedInvoiceRepositoryInterface::class),
                    self::service($c, VendorRepositoryInterface::class),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                VoidReceivedInvoiceUseCaseInterface::class,
                static fn (ContainerInterface $c): VoidReceivedInvoiceUseCase => new VoidReceivedInvoiceUseCase(
                    self::service($c, ReceivedInvoiceRepositoryInterface::class),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                PdfStorageInterface::class,
                static fn (ContainerInterface $c): PdfStorageInterface => new LocalPdfStorage(self::projectRoot($c) . '/var/storage'),
            )
            ->set(
                AttachReceivedInvoicePdfUseCaseInterface::class,
                static fn (ContainerInterface $c): AttachReceivedInvoicePdfUseCase => new AttachReceivedInvoicePdfUseCase(
                    self::service($c, ReceivedInvoiceRepositoryInterface::class),
                    self::service($c, PdfStorageInterface::class),
                    self::tx($c),
                    self::invoicesFactory($c),
                    AuditServiceProvider::recorderFactory($c),
                    self::orgHolder($c),
                ),
            )
            ->set(
                ListReceivedInvoicesHandler::class,
                static fn (ContainerInterface $c): ListReceivedInvoicesHandler
                    => new ListReceivedInvoicesHandler(self::service($c, ListReceivedInvoicesUseCaseInterface::class), self::json($c)),
            )
            ->set(
                GetReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): GetReceivedInvoiceHandler
                    => new GetReceivedInvoiceHandler(self::service($c, GetReceivedInvoiceUseCaseInterface::class), self::json($c)),
            )
            ->set(
                CreateReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): CreateReceivedInvoiceHandler
                    => new CreateReceivedInvoiceHandler(self::service($c, CreateReceivedInvoiceUseCaseInterface::class), self::json($c)),
            )
            ->set(
                UpdateReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): UpdateReceivedInvoiceHandler
                    => new UpdateReceivedInvoiceHandler(self::service($c, UpdateReceivedInvoiceUseCaseInterface::class), self::json($c)),
            )
            ->set(
                VoidReceivedInvoiceHandler::class,
                static fn (ContainerInterface $c): VoidReceivedInvoiceHandler
                    => new VoidReceivedInvoiceHandler(self::service($c, VoidReceivedInvoiceUseCaseInterface::class), self::json($c)),
            )
            ->set(
                AttachReceivedInvoicePdfHandler::class,
                static fn (ContainerInterface $c): AttachReceivedInvoicePdfHandler
                    => new AttachReceivedInvoicePdfHandler(self::service($c, AttachReceivedInvoicePdfUseCaseInterface::class), self::json($c)),
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
                    self::service($c, ListReceivedInvoicesHandler::class),
                    self::service($c, GetReceivedInvoiceHandler::class),
                    self::service($c, CreateReceivedInvoiceHandler::class),
                    self::service($c, UpdateReceivedInvoiceHandler::class),
                    self::service($c, VoidReceivedInvoiceHandler::class),
                    self::service($c, AttachReceivedInvoicePdfHandler::class),
                ),
            );
    }

    private static function projectRoot(ContainerInterface $c): string
    {
        $root = $c->get(RuntimeServiceProvider::PROJECT_ROOT);

        if (!is_string($root) || $root === '') {
            throw new LogicException('Project root service is invalid.');
        }

        return $root;
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
