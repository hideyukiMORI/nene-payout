<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use Nene2\Audit\AuditEvent;
use NenePayout\Audit\AuditLogFilter;
use NenePayout\Audit\AuditLogView;
use NenePayout\Audit\ListAuditLogsUseCase;
use PHPUnit\Framework\TestCase;

final class ListAuditLogsUseCaseTest extends TestCase
{
    public function test_lists_and_filters_by_entity_type(): void
    {
        $repo = new InMemoryAuditReadRepository();
        $repo->add(new AuditLogView(new AuditEvent(action: 'vendor.created', entityType: 'vendor', id: '01A')));
        $repo->add(new AuditLogView(new AuditEvent(action: 'received_invoice.created', entityType: 'received_invoice', id: '01B')));

        $useCase = new ListAuditLogsUseCase($repo);

        $all = $useCase->execute(new AuditLogFilter(), 20, 0);
        self::assertSame(2, $all->total);
        self::assertCount(2, $all->items);

        $vendorsOnly = $useCase->execute(new AuditLogFilter(entityType: 'vendor'), 20, 0);
        self::assertSame(1, $vendorsOnly->total);
        self::assertSame('vendor.created', $vendorsOnly->items[0]->event->action);
    }
}
