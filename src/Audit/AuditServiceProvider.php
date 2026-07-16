<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use LogicException;
use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditPayloadMode;
use Nene2\Audit\AuditRecorderFactory;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Audit\AuditTableConfig;
use Nene2\Audit\PdoAuditEventRepository;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\DependencyInjection\ContainerBuilder;
use Nene2\DependencyInjection\ServiceProviderInterface;
use Nene2\Http\ClockInterface;
use Nene2\Http\JsonResponseFactory;
use Nene2\Http\RequestScopedHolder;
use NenePayout\ApplicationServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Wires the framework audit module (`Nene2\Audit`, ADR 0014) onto Payout's
 * existing `audit_logs` table — no re-migration.
 *
 * The whole product/framework seam is {@see AuditTableConfig}: it points the
 * framework repository and recorder at Payout's physical columns (ULID string
 * id, `actor_user_id`, `created_at`, `before_json`/`after_json`, and the
 * `request_id` column reused as the framework `metadata` receptacle). Records
 * are written by the framework's transaction-atomic
 * {@see AuditRecorderFactoryInterface::forExecutor()}; reads go through the
 * product's {@see AuditReadRepositoryInterface}, which keeps the org-scoping and
 * actor-email concerns the framework contract intentionally omits.
 */
final readonly class AuditServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder
            ->set(
                AuditTableConfig::class,
                static fn (): AuditTableConfig => self::tableConfig(),
            )
            // Non-transactional repository, used by the read side. Mutating use
            // cases build their own repository bound to the transaction executor
            // via AuditRecorderFactoryInterface::forExecutor().
            ->set(
                AuditEventRepositoryInterface::class,
                static function (ContainerInterface $container): AuditEventRepositoryInterface {
                    return new PdoAuditEventRepository(self::query($container), self::tableConfig());
                },
            )
            ->set(
                AuditRecorderFactoryInterface::class,
                static function (ContainerInterface $container): AuditRecorderFactoryInterface {
                    // No organization holder is passed: every Payout use case sets
                    // AuditEvent::$organizationId explicitly (including holder-less
                    // superadmin provisioning), so the recorder never needs the
                    // fallback. Payout's holder is also RequestScopedHolder<string>,
                    // which is invariant against the framework's <string|int>.
                    return new AuditRecorderFactory(self::clock($container), self::tableConfig());
                },
            )
            ->set(
                AuditReadRepositoryInterface::class,
                static function (ContainerInterface $container): AuditReadRepositoryInterface {
                    $events = $container->get(AuditEventRepositoryInterface::class);

                    if (!$events instanceof AuditEventRepositoryInterface) {
                        throw new LogicException('Audit event repository service is invalid.');
                    }

                    return new PdoAuditReadRepository(
                        $events,
                        self::query($container),
                        self::orgHolder($container),
                    );
                },
            )
            ->set(
                ListAuditLogsUseCaseInterface::class,
                static function (ContainerInterface $container): ListAuditLogsUseCaseInterface {
                    $repo = $container->get(AuditReadRepositoryInterface::class);

                    if (!$repo instanceof AuditReadRepositoryInterface) {
                        throw new LogicException('Audit read repository service is invalid.');
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

    /**
     * Points the framework audit module at Payout's existing `audit_logs` table
     * (ADR 0014). This is the single knob a product turns to adopt `Nene2\Audit`
     * without re-migrating: physical column names, ULID string id
     * (`idIsAutoIncrement: false`), and canonical before/after payload mode.
     */
    private static function tableConfig(): AuditTableConfig
    {
        return new AuditTableConfig(
            table: 'audit_logs',
            mode: AuditPayloadMode::BeforeAfter,
            idColumn: 'id',
            actionColumn: 'action',
            entityTypeColumn: 'entity_type',
            entityIdColumn: 'entity_id',
            actorColumn: 'actor_user_id',
            organizationColumn: 'organization_id',
            occurredAtColumn: 'created_at',
            metadataColumn: 'request_id',
            beforeColumn: 'before_json',
            afterColumn: 'after_json',
            payloadColumn: null,
            idIsAutoIncrement: false,
        );
    }

    private static function query(ContainerInterface $container): DatabaseQueryExecutorInterface
    {
        $query = $container->get(DatabaseQueryExecutorInterface::class);

        if (!$query instanceof DatabaseQueryExecutorInterface) {
            throw new LogicException('Database query executor service is invalid.');
        }

        return $query;
    }

    private static function clock(ContainerInterface $container): ClockInterface
    {
        $clock = $container->get(ClockInterface::class);

        if (!$clock instanceof ClockInterface) {
            throw new LogicException('Clock service is invalid.');
        }

        return $clock;
    }

    /**
     * @return RequestScopedHolder<string>
     */
    private static function orgHolder(ContainerInterface $container): RequestScopedHolder
    {
        $holder = $container->get(ApplicationServiceProvider::ORG_ID_HOLDER);

        if (!$holder instanceof RequestScopedHolder) {
            throw new LogicException('Org id holder service is invalid.');
        }

        /** @var RequestScopedHolder<string> $holder */
        return $holder;
    }
}
