<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditEventRepositoryInterface;
use Nene2\Audit\AuditQuery;
use Nene2\Audit\AuditRecorder;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Audit\AuditRecorderInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\ClockInterface;

/**
 * Test double for the framework audit recorder factory (ADR 0014).
 *
 * Wraps the real {@see AuditRecorder} over an in-memory event store so use-case
 * tests observe exactly what production records (occurredAt filled from the
 * injected clock, id supplied by the call site) without a database. Every
 * `forExecutor()` writes into the same {@see $appended} list, mirroring the
 * transaction-atomic factory.
 */
final class InMemoryAuditRecorderFactory implements AuditRecorderFactoryInterface, AuditEventRepositoryInterface
{
    /** @var list<AuditEvent> */
    public array $appended = [];

    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function forExecutor(DatabaseQueryExecutorInterface $executor): AuditRecorderInterface
    {
        return new AuditRecorder($this, $this->clock);
    }

    public function append(AuditEvent $event): void
    {
        $this->appended[] = $event;
    }

    /** @return list<AuditEvent> */
    public function query(AuditQuery $query, int $limit, int $offset): array
    {
        return array_slice($this->appended, $offset, $limit);
    }

    public function count(AuditQuery $query): int
    {
        return count($this->appended);
    }
}
