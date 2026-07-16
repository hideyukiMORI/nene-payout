<?php

declare(strict_types=1);

namespace NenePayout\Tests\Vendor;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use NenePayout\Vendor\CreateVendorInput;
use NenePayout\Vendor\CreateVendorUseCase;
use NenePayout\Vendor\DeactivateVendorUseCase;
use NenePayout\Vendor\UpdateVendorInput;
use NenePayout\Vendor\UpdateVendorUseCase;
use NenePayout\Vendor\Vendor;
use NenePayout\Vendor\VendorNotFoundException;
use NenePayout\Vendor\VendorRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class VendorUseCaseTest extends TestCase
{
    private InMemoryAuditRecorderFactory $auditRepo;

    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $holder->set('01ORG00000000000000000001');
        $this->orgId = $holder;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): VendorRepositoryInterface */
    private function vendorsFactory(VendorRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): VendorRepositoryInterface => $repo;
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function createInput(string $name = '仕入先株式会社'): CreateVendorInput
    {
        return new CreateVendorInput(
            name: $name,
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1234567',
            accountName: 'シイレサキ',
            registrationNumber: 'T1234567890123',
        );
    }

    public function test_create_persists_vendor_and_records_audit(): void
    {
        $repo = new InMemoryVendorRepository();
        $useCase = new CreateVendorUseCase(
            new ImmediateTransactionManager(),
            $this->vendorsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $vendor = $useCase->execute('01USER0000000000000000001', $this->createInput());

        self::assertNotNull($vendor->id);
        self::assertSame('仕入先株式会社', $vendor->name);
        self::assertSame('01ORG00000000000000000001', $vendor->organizationId);

        self::assertCount(1, $this->auditRepo->appended);
        $log = $this->auditRepo->appended[0];
        self::assertSame('vendor.created', $log->action);
        self::assertNull($log->before);
        self::assertSame('仕入先株式会社', $log->after['name'] ?? null);
    }

    public function test_update_records_before_and_after(): void
    {
        $existing = new Vendor(
            name: 'Old',
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1234567',
            accountName: 'オールド',
            isActive: true,
            organizationId: '01ORG00000000000000000001',
            id: '01VENDOR000000000000000001',
        );
        $repo = new InMemoryVendorRepository($existing);

        $useCase = new UpdateVendorUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->vendorsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $updated = $useCase->execute('01USER0000000000000000001', '01VENDOR000000000000000001', new UpdateVendorInput(
            name: 'New',
            bankCode: '0001',
            branchCode: '001',
            accountType: '当座',
            accountNumber: '7654321',
            accountName: 'ニュー',
            registrationNumber: null,
        ));

        self::assertSame('New', $updated->name);
        self::assertSame('当座', $updated->accountType);

        $log = $this->auditRepo->appended[0];
        self::assertSame('vendor.updated', $log->action);
        self::assertSame('Old', $log->before['name'] ?? null);
        self::assertSame('New', $log->after['name'] ?? null);
    }

    public function test_update_unknown_vendor_throws(): void
    {
        $repo = new InMemoryVendorRepository();
        $useCase = new UpdateVendorUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->vendorsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(VendorNotFoundException::class);
        $useCase->execute(null, 'missing', new UpdateVendorInput(
            name: 'X',
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1',
            accountName: 'X',
        ));
    }

    public function test_deactivate_soft_deletes_and_records_audit(): void
    {
        $existing = new Vendor(
            name: 'Active',
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1234567',
            accountName: 'アクティブ',
            isActive: true,
            organizationId: '01ORG00000000000000000001',
            id: '01VENDOR000000000000000001',
        );
        $repo = new InMemoryVendorRepository($existing);

        $useCase = new DeactivateVendorUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->vendorsFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $deactivated = $useCase->execute('01USER0000000000000000001', '01VENDOR000000000000000001');

        self::assertFalse($deactivated->isActive);
        self::assertNull($repo->findById('01VENDOR000000000000000001'));

        $log = $this->auditRepo->appended[0];
        self::assertSame('vendor.deactivated', $log->action);
        self::assertSame('Active', $log->before['name'] ?? null);
        self::assertNull($log->after);
    }
}
