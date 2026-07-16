<?php

declare(strict_types=1);

namespace NenePayout\Tests\Organization;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Organization\GetOrganizationUseCase;
use NenePayout\Organization\Organization;
use NenePayout\Organization\OrganizationNotFoundException;
use NenePayout\Organization\OrganizationRepositoryInterface;
use NenePayout\Organization\UpdateOrganizationInput;
use NenePayout\Organization\UpdateOrganizationUseCase;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class OrganizationUseCaseTest extends TestCase
{
    private const ORG_ID = '01ORG00000000000000000001';

    private InMemoryAuditRecorderFactory $auditRepo;

    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $holder->set(self::ORG_ID);
        $this->orgId = $holder;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): OrganizationRepositoryInterface */
    private function organizationsFactory(OrganizationRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): OrganizationRepositoryInterface => $repo;
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function existing(string $name = 'Acme 株式会社'): Organization
    {
        return new Organization(
            slug: 'acme',
            name: $name,
            isActive: true,
            id: self::ORG_ID,
            customDomain: 'pay.acme.example',
            createdAt: '2026-06-13 00:00:00',
            updatedAt: '2026-06-13 00:00:00',
        );
    }

    public function test_get_returns_current_tenant_organization(): void
    {
        $repo = new InMemoryOrganizationRepository($this->existing());
        $useCase = new GetOrganizationUseCase($repo, $this->orgId);

        $org = $useCase->execute();

        self::assertSame(self::ORG_ID, $org->id);
        self::assertSame('acme', $org->slug);
        self::assertSame('Acme 株式会社', $org->name);
    }

    public function test_get_throws_when_organization_missing(): void
    {
        $useCase = new GetOrganizationUseCase(new InMemoryOrganizationRepository(), $this->orgId);

        $this->expectException(OrganizationNotFoundException::class);
        $useCase->execute();
    }

    public function test_update_changes_name_and_records_before_after(): void
    {
        $repo = new InMemoryOrganizationRepository($this->existing('Old Name'));
        $useCase = new UpdateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $updated = $useCase->execute('01USER0000000000000000001', new UpdateOrganizationInput('New Name'));

        self::assertSame('New Name', $updated->name);
        // slug / custom_domain are preserved (not mutable here).
        self::assertSame('acme', $updated->slug);
        self::assertSame('pay.acme.example', $updated->customDomain);

        self::assertCount(1, $this->auditRepo->appended);
        $log = $this->auditRepo->appended[0];
        self::assertSame('organization.updated', $log->action);
        self::assertSame('Old Name', $log->before['name'] ?? null);
        self::assertSame('New Name', $log->after['name'] ?? null);
    }

    public function test_update_throws_when_organization_missing(): void
    {
        $repo = new InMemoryOrganizationRepository();
        $useCase = new UpdateOrganizationUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->organizationsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(OrganizationNotFoundException::class);
        $useCase->execute(null, new UpdateOrganizationInput('New Name'));
    }
}
