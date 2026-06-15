<?php

declare(strict_types=1);

use NenePayout\Support\Ulid;
use Phinx\Seed\AbstractSeed;

/**
 * Seeds a default admin user for local development so the login flow can be
 * exercised end-to-end (docker compose up + `composer migrations:seed`).
 *
 * Belongs to the seeded default organization (see DefaultOrganizationSeed, which
 * this depends on). Email/password are overridable via `SEED_ADMIN_EMAIL` /
 * `SEED_ADMIN_PASSWORD`; defaults are `admin@payout.test` / `password`. The
 * password is stored as a bcrypt hash — the plaintext is never persisted.
 * Idempotent: skips when a user with the same email already exists. Not for
 * production.
 */
final class DefaultAdminUserSeed extends AbstractSeed
{
    /**
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return ['DefaultOrganizationSeed'];
    }

    public function run(): void
    {
        $slug = getenv('ORG_SLUG') ?: 'payout';

        $email = getenv('SEED_ADMIN_EMAIL') ?: 'admin@payout.test';
        $plaintext = getenv('SEED_ADMIN_PASSWORD') ?: 'password';

        $organizationId = $this->resolveOrganizationId($slug);

        if ($organizationId === null) {
            // Org seed missing — nothing to attach the user to.
            return;
        }

        foreach ($this->fetchAll('SELECT email FROM users') as $row) {
            if ($row['email'] === $email) {
                return;
            }
        }

        $now = date('Y-m-d H:i:s');

        $this->table('users')
            ->insert([
                'id' => Ulid::generate(),
                'organization_id' => $organizationId,
                'email' => $email,
                'password_hash' => password_hash($plaintext, PASSWORD_DEFAULT),
                'role' => 'admin',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->saveData();
    }

    private function resolveOrganizationId(string $slug): ?string
    {
        foreach ($this->fetchAll('SELECT id, slug FROM organizations') as $row) {
            if ($row['slug'] === $slug) {
                return (string) $row['id'];
            }
        }

        return null;
    }
}
