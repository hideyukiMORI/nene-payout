<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use NenePayout\Audit\AuditRecorder;
use NenePayout\Tests\Support\FixedClock;
use PHPUnit\Framework\TestCase;

final class AuditRecorderTest extends TestCase
{
    public function test_records_a_sanitized_before_after_entry(): void
    {
        $repo = new InMemoryAuditLogRepository();
        $recorder = new AuditRecorder($repo, new FixedClock('2026-06-13T01:02:03+00:00'));

        $recorder->record(
            actorUserId: '01USER0000000000000000001',
            organizationId: '01ORG00000000000000000001',
            action: 'vendor.updated',
            entityType: 'vendor',
            entityId: '01VENDOR000000000000000001',
            before: ['name' => 'Old'],
            after: ['name' => 'New'],
        );

        self::assertCount(1, $repo->appended);
        $log = $repo->appended[0];

        self::assertSame('vendor.updated', $log->action);
        self::assertSame('vendor', $log->entityType);
        self::assertSame('01USER0000000000000000001', $log->actorUserId);
        self::assertSame('01ORG00000000000000000001', $log->organizationId);
        self::assertSame(['name' => 'Old'], $log->before);
        self::assertSame(['name' => 'New'], $log->after);
        self::assertSame('2026-06-13 01:02:03', $log->createdAt);
        self::assertNotNull($log->id);
        self::assertSame(26, strlen((string) $log->id));
    }
}
