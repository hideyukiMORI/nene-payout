<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use Closure;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use Nene2\Validation\ValidationException;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceInput;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceUseCase;
use NenePayout\ReceivedInvoice\InvoiceNotEditableException;
use NenePayout\ReceivedInvoice\ReceivedInvoice;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\ReceivedInvoice\UpdateReceivedInvoiceInput;
use NenePayout\ReceivedInvoice\UpdateReceivedInvoiceUseCase;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use NenePayout\Tests\Vendor\InMemoryVendorRepository;
use NenePayout\Vendor\Vendor;
use PHPUnit\Framework\TestCase;

final class ReceivedInvoiceUseCaseTest extends TestCase
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

    /** @return Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface */
    private function invoicesFactory(ReceivedInvoiceRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface => $repo;
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function vendors(): InMemoryVendorRepository
    {
        return new InMemoryVendorRepository(new Vendor(
            name: 'Vendor',
            bankCode: '0001',
            branchCode: '001',
            accountType: '普通',
            accountNumber: '1234567',
            accountName: 'ベンダー',
            isActive: true,
            organizationId: '01ORG00000000000000000001',
            id: '01VENDOR000000000000000001',
        ));
    }

    private function createInput(): CreateReceivedInvoiceInput
    {
        return new CreateReceivedInvoiceInput(
            vendorId: '01VENDOR000000000000000001',
            amount: 100000,
            dueDate: '2026-07-31',
            taxBreakdown: [['tax_rate_bps' => 1000, 'taxable_amount' => 100000, 'tax_amount' => 10000]],
            registrationNumber: 'T1234567890123',
        );
    }

    public function test_create_persists_pending_invoice_and_records_audit(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository();
        $useCase = new CreateReceivedInvoiceUseCase(
            $this->vendors(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $invoice = $useCase->execute('01USER0000000000000000001', $this->createInput());

        self::assertSame('pending', $invoice->status);
        self::assertSame(100000, $invoice->amount);
        self::assertCount(1, $this->auditRepo->appended);
        self::assertSame('received_invoice.created', $this->auditRepo->appended[0]->action);
    }

    public function test_create_with_unknown_vendor_fails_validation(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository();
        $useCase = new CreateReceivedInvoiceUseCase(
            new InMemoryVendorRepository(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(ValidationException::class);
        $useCase->execute(null, $this->createInput());
    }

    public function test_update_rejected_when_not_pending(): void
    {
        $existing = new ReceivedInvoice(
            vendorId: '01VENDOR000000000000000001',
            amount: 100000,
            dueDate: '2026-07-31',
            status: 'paid',
            organizationId: '01ORG00000000000000000001',
            id: '01INV00000000000000000001',
        );
        $repo = new InMemoryReceivedInvoiceRepository($existing);

        $useCase = new UpdateReceivedInvoiceUseCase(
            $repo,
            $this->vendors(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(InvoiceNotEditableException::class);
        $useCase->execute(null, '01INV00000000000000000001', new UpdateReceivedInvoiceInput(
            vendorId: '01VENDOR000000000000000001',
            amount: 200000,
            dueDate: '2026-08-31',
        ));
    }

    public function test_update_pending_records_before_after(): void
    {
        $existing = new ReceivedInvoice(
            vendorId: '01VENDOR000000000000000001',
            amount: 100000,
            dueDate: '2026-07-31',
            status: 'pending',
            organizationId: '01ORG00000000000000000001',
            id: '01INV00000000000000000001',
        );
        $repo = new InMemoryReceivedInvoiceRepository($existing);

        $useCase = new UpdateReceivedInvoiceUseCase(
            $repo,
            $this->vendors(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $updated = $useCase->execute('01USER0000000000000000001', '01INV00000000000000000001', new UpdateReceivedInvoiceInput(
            vendorId: '01VENDOR000000000000000001',
            amount: 250000,
            dueDate: '2026-09-30',
        ));

        self::assertSame(250000, $updated->amount);
        self::assertSame('received_invoice.updated', $this->auditRepo->appended[0]->action);
        self::assertSame(100000, $this->auditRepo->appended[0]->before['amount'] ?? null);
        self::assertSame(250000, $this->auditRepo->appended[0]->after['amount'] ?? null);
    }
}
