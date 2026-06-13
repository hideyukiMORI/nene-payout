<?php

declare(strict_types=1);

namespace NenePayout\Tests\Audit;

use NenePayout\Audit\AuditLogFilterFactory;
use PHPUnit\Framework\TestCase;

final class AuditLogFilterFactoryTest extends TestCase
{
    public function test_maps_all_known_query_params(): void
    {
        $filter = AuditLogFilterFactory::fromQueryParams([
            'entity_type' => 'vendor',
            'entity_id' => '01V',
            'actor_user_id' => '01U',
            'action' => 'vendor.updated',
            'from' => '2026-06-01 00:00:00',
            'to' => '2026-06-30 23:59:59',
        ]);

        self::assertSame('vendor', $filter->entityType);
        self::assertSame('01V', $filter->entityId);
        self::assertSame('01U', $filter->actorUserId);
        self::assertSame('vendor.updated', $filter->action);
        self::assertSame('2026-06-01 00:00:00', $filter->createdFrom);
        self::assertSame('2026-06-30 23:59:59', $filter->createdTo);
    }

    public function test_empty_query_yields_all_null(): void
    {
        $filter = AuditLogFilterFactory::fromQueryParams([]);

        self::assertNull($filter->entityType);
        self::assertNull($filter->entityId);
        self::assertNull($filter->actorUserId);
        self::assertNull($filter->action);
        self::assertNull($filter->createdFrom);
        self::assertNull($filter->createdTo);
    }

    public function test_empty_strings_and_non_strings_become_null(): void
    {
        $filter = AuditLogFilterFactory::fromQueryParams([
            'entity_type' => '',
            'entity_id' => ['array'],
            'actor_user_id' => 123,
            'action' => null,
        ]);

        self::assertNull($filter->entityType);
        self::assertNull($filter->entityId);
        self::assertNull($filter->actorUserId);
        self::assertNull($filter->action);
    }
}
