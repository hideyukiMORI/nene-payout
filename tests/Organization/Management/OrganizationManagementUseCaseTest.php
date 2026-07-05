<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization\Management;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use NenePayout\Organization\Management\CreateOrganizationInput;
use NenePayout\Organization\Management\CreateOrganizationUseCase;
use NenePayout\Organization\Management\DeactivateOrganizationUseCase;
use NenePayout\Organization\Management\GetOrganizationUseCase;
use NenePayout\Organization\Management\ListOrganizationsUseCase;
use NenePayout\Organization\Management\OrganizationSlugConflictException;
use NenePayout\Organization\Management\UpdateOrganizationInput;
use NenePayout\Organization\Management\UpdateOrganizationUseCase;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\Organization\InMemoryOrganizationRepository;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class OrganizationManagementUseCaseTest extends TestCase
{
    private InMemoryAuditRecorderFactory $auditRepo;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface */
    private function factory(OrganizationRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationRepositoryInterface => $repo;
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function org(string $id, string $slug, string $name = 'Example Co.', ?string $domain = null, bool $active = true): Organization
    {
        return new Organization(
            slug: $slug,
            name: $name,
            isActive: $active,
            id: $id,
            customDomain: $domain,
            createdAt: '2026-06-13 00:00:00',
            updatedAt: '2026-06-13 00:00:00',
        );
    }

    public function test_list_returns_all_organizations_with_total(): void
    {
        $repo = new InMemoryOrganizationRepository(
            $this->org('01A', 'alpha'),
            $this->org('01B', 'bravo'),
        );
        $useCase = new ListOrganizationsUseCase($repo);

        $result = $useCase->execute(20, 0);

        self::assertCount(2, $result->items);
        self::assertSame(2, $result->total);
    }

    public function test_get_returns_organization_by_id(): void
    {
        $repo = new InMemoryOrganizationRepository($this->org('01A', 'alpha'));
        $useCase = new GetOrganizationUseCase($repo);

        self::assertSame('alpha', $useCase->execute('01A')->slug);
    }

    public function test_get_unknown_throws_not_found(): void
    {
        $useCase = new GetOrganizationUseCase(new InMemoryOrganizationRepository());

        $this->expectException(OrganizationNotFoundException::class);
        $useCase->execute('missing');
    }

    public function test_create_persists_and_records_audit(): void
    {
        $repo = new InMemoryOrganizationRepository();
        $useCase = new CreateOrganizationUseCase(
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $created = $useCase->execute('01USER0000000000000000001', new CreateOrganizationInput('newco', 'New Co.', null));

        self::assertNotSame('', $created->id);
        self::assertSame('newco', $created->slug);
        self::assertTrue($created->isActive);
        self::assertNotNull($repo->findBySlug('newco'));

        self::assertCount(1, $this->auditRepo->appended);
        $log = $this->auditRepo->appended[0];
        self::assertSame('organization.created', $log->action);
        self::assertSame($created->id, $log->organizationId);
        self::assertNull($log->before);
        self::assertSame('newco', $log->after['slug'] ?? null);
    }

    public function test_create_rejects_duplicate_slug_with_conflict(): void
    {
        $repo = new InMemoryOrganizationRepository($this->org('01A', 'dupe'));
        $useCase = new CreateOrganizationUseCase(
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $this->expectException(OrganizationSlugConflictException::class);
        $useCase->execute(null, new CreateOrganizationInput('dupe', 'Dup Co.', null));
    }

    public function test_create_rejects_duplicate_custom_domain_with_conflict(): void
    {
        $repo = new InMemoryOrganizationRepository($this->org('01A', 'alpha', 'Alpha', 'pay.alpha.example'));
        $useCase = new CreateOrganizationUseCase(
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $this->expectException(OrganizationSlugConflictException::class);
        $useCase->execute(null, new CreateOrganizationInput('beta', 'Beta', 'pay.alpha.example'));
    }

    public function test_update_changes_name_and_domain_and_records_before_after(): void
    {
        $repo = new InMemoryOrganizationRepository($this->org('01A', 'alpha', 'Old', null));
        $useCase = new UpdateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $updated = $useCase->execute('01USER0000000000000000001', '01A', new UpdateOrganizationInput('New', 'pay.alpha.example'));

        self::assertSame('New', $updated->name);
        self::assertSame('pay.alpha.example', $updated->customDomain);
        self::assertSame('alpha', $updated->slug); // immutable

        $log = $this->auditRepo->appended[0];
        self::assertSame('organization.updated', $log->action);
        self::assertSame('Old', $log->before['name'] ?? null);
        self::assertSame('New', $log->after['name'] ?? null);
    }

    public function test_update_unknown_throws_not_found(): void
    {
        $repo = new InMemoryOrganizationRepository();
        $useCase = new UpdateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $this->expectException(OrganizationNotFoundException::class);
        $useCase->execute(null, 'missing', new UpdateOrganizationInput('New', null));
    }

    public function test_update_rejects_domain_taken_by_another_org(): void
    {
        $repo = new InMemoryOrganizationRepository(
            $this->org('01A', 'alpha', 'Alpha', null),
            $this->org('01B', 'bravo', 'Bravo', 'pay.bravo.example'),
        );
        $useCase = new UpdateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $this->expectException(OrganizationSlugConflictException::class);
        $useCase->execute(null, '01A', new UpdateOrganizationInput('Alpha', 'pay.bravo.example'));
    }

    public function test_deactivate_soft_disables_and_records_audit(): void
    {
        $repo = new InMemoryOrganizationRepository($this->org('01A', 'alpha'));
        $useCase = new DeactivateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->factory($repo),
            $this->auditFactory(),
        );

        $deactivated = $useCase->execute('01USER0000000000000000001', '01A');

        self::assertFalse($deactivated->isActive);
        self::assertFalse($repo->findById('01A')?->isActive);

        $log = $this->auditRepo->appended[0];
        self::assertSame('organization.deactivated', $log->action);
        self::assertSame('alpha', $log->before['slug'] ?? null);
        self::assertNull($log->after);
    }

    public function test_deactivate_unknown_throws_not_found(): void
    {
        $useCase = new DeactivateOrganizationUseCase(
            new InMemoryOrganizationRepository(),
            new ImmediateTransactionManager(),
            $this->factory(new InMemoryOrganizationRepository()),
            $this->auditFactory(),
        );

        $this->expectException(OrganizationNotFoundException::class);
        $useCase->execute(null, 'missing');
    }
}
