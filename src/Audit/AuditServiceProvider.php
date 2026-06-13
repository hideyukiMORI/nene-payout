<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use LogicException;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\ApplicationServiceProvider;
use Psr\Container\ContainerInterface;

final readonly class AuditServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                AuditLogRepositoryInterface::class,
                static function (ContainerInterface $container): AuditLogRepositoryInterface {
                    $query = $container->get(DatabaseQueryExecutorInterface::class);
                    $orgId = $container->get(ApplicationServiceProvider::ORG_ID_HOLDER);

                    if (!$query instanceof DatabaseQueryExecutorInterface) {
                        throw new LogicException('Database query executor service is invalid.');
                    }

                    if (!$orgId instanceof RequestScopedHolder) {
                        throw new LogicException('Org id holder service is invalid.');
                    }

                    /** @var RequestScopedHolder<string> $orgId */
                    return new PdoAuditLogRepository($query, $orgId);
                },
            )
            ->set(
                AuditRecorderInterface::class,
                static function (ContainerInterface $container): AuditRecorderInterface {
                    $repo = $container->get(AuditLogRepositoryInterface::class);
                    $clock = $container->get(ClockInterface::class);

                    if (!$repo instanceof AuditLogRepositoryInterface) {
                        throw new LogicException('Audit log repository service is invalid.');
                    }

                    if (!$clock instanceof ClockInterface) {
                        throw new LogicException('Clock service is invalid.');
                    }

                    return new AuditRecorder($repo, $clock);
                },
            )
            ->set(
                ListAuditLogsUseCaseInterface::class,
                static function (ContainerInterface $container): ListAuditLogsUseCaseInterface {
                    $repo = $container->get(AuditLogRepositoryInterface::class);

                    if (!$repo instanceof AuditLogRepositoryInterface) {
                        throw new LogicException('Audit log repository service is invalid.');
                    }

                    return new ListAuditLogsUseCase($repo);
                },
            )
            ->set(
                ListAuditLogsHandler::class,
                static function (ContainerInterface $container): ListAuditLogsHandler {
                    $useCase = $container->get(ListAuditLogsUseCaseInterface::class);
                    $response = $container->get(JsonResponseFactory::class);

                    if (!$useCase instanceof ListAuditLogsUseCaseInterface) {
                        throw new LogicException('List audit logs use case service is invalid.');
                    }

                    if (!$response instanceof JsonResponseFactory) {
                        throw new LogicException('JSON response factory service is invalid.');
                    }

                    return new ListAuditLogsHandler($useCase, $response);
                },
            )
            ->set(
                AuditRouteRegistrar::class,
                static function (ContainerInterface $container): AuditRouteRegistrar {
                    $list = $container->get(ListAuditLogsHandler::class);

                    if (!$list instanceof ListAuditLogsHandler) {
                        throw new LogicException('List audit logs handler service is invalid.');
                    }

                    return new AuditRouteRegistrar($list);
                },
            );
    }
}
