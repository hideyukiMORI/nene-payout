<?php

declare(strict_types=1);

namespace NenePayout\Audit;

use Nene2\Audit\AuditEvent;

/**
 * Read-side view of one audit trail entry: a framework {@see AuditEvent} plus
 * the actor's email, resolved at read time via a `users` lookup and never
 * persisted on the trail. The write side records `Nene2\Audit\AuditEvent`
 * directly (ADR 0014); this view exists only for the product's list endpoint.
 */
final readonly class AuditLogView
{
    public function __construct(
        public AuditEvent $event,
        public ?string $actorEmail = null,
    ) {
    }
}
