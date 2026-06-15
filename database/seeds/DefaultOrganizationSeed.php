<?php

declare(strict_types=1);

use NenePayout\Support\Ulid;
use Phinx\Seed\AbstractSeed;

/**
 * Seeds the default single-tenant organization for local development.
 *
 * Single-tenant resolution (`TENANT_RESOLUTION=single`, `ORG_SLUG`) needs a
 * matching row in `organizations`; without it every `/api/v1/*` request returns
 * `org-not-found`. Idempotent: skips when the slug already exists. Not for
 * production — see docs/explanation/multi-tenancy.md (ADR 0018).
 */
final class DefaultOrganizationSeed extends AbstractSeed
{
    public function run(): void
    {
        $slug = getenv('ORG_SLUG') ?: 'payout';
        $name = getenv('APP_NAME') ?: 'NeNe Payout';

        foreach ($this->fetchAll('SELECT slug FROM organizations') as $row) {
            if ($row['slug'] === $slug) {
                return;
            }
        }

        $now = date('Y-m-d H:i:s');

        $this->table('organizations')
            ->insert([
                'id' => Ulid::generate(),
                'slug' => $slug,
                'name' => $name,
                'custom_domain' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->saveData();
    }
}
