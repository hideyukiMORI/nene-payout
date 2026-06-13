<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorder;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\ReceivedInvoice\GetReceivedInvoiceUseCase;
use NenePayout\ReceivedInvoice\InvoiceNotEditableException;
use NenePayout\ReceivedInvoice\ListReceivedInvoicesUseCase;
use NenePayout\ReceivedInvoice\ReceivedInvoice;
use NenePayout\ReceivedInvoice\ReceivedInvoiceFilter;
use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundException;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\ReceivedInvoice\VoidReceivedInvoiceUseCase;
use NenePayout\Tests\Audit\InMemoryAuditLogRepository;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class ReceivedInvoiceQueryUseCaseTest extends TestCase
{
    private function invoice(string $id, string $status = 'pending', string $vendorId = '01V'): ReceivedInvoice
    {
        return new ReceivedInvoice(
            vendorId: $vendorId,
            amount: 100000,
            dueDate: '2026-07-31',
            status: $status,
            organizationId: '01ORG00000000000000000001',
            id: $id,
        );
    }

    public function test_list_default_excludes_voided(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository(
            $this->invoice('01A', 'pending'),
            $this->invoice('01B', 'voided'),
        );

        $out = (new ListReceivedInvoicesUseCase($repo))->execute(new ReceivedInvoiceFilter(), 20, 0);

        self::assertSame(1, $out->total);
    }

    public function test_list_status_filter_can_select_voided(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository(
            $this->invoice('01A', 'pending'),
            $this->invoice('01B', 'voided'),
        );

        $out = (new ListReceivedInvoicesUseCase($repo))->execute(new ReceivedInvoiceFilter(status: 'voided'), 20, 0);

        self::assertSame(1, $out->total);
        self::assertSame('voided', $out->items[0]->status);
    }

    public function test_list_vendor_filter(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository(
            $this->invoice('01A', 'pending', '01V1'),
            $this->invoice('01B', 'pending', '01V2'),
        );

        $out = (new ListReceivedInvoicesUseCase($repo))->execute(new ReceivedInvoiceFilter(vendorId: '01V2'), 20, 0);

        self::assertSame(1, $out->total);
        self::assertSame('01V2', $out->items[0]->vendorId);
    }

    public function test_get_throws_when_missing(): void
    {
        $useCase = new GetReceivedInvoiceUseCase(new InMemoryReceivedInvoiceRepository());

        $this->expectException(ReceivedInvoiceNotFoundException::class);
        $useCase->execute('missing');
    }

    /** @return Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface */
    private function invoicesFactory(ReceivedInvoiceRepositoryInterface $repo): Closure
    {
        return static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface => $repo;
    }

    /** @return Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface */
    private function auditFactory(InMemoryAuditLogRepository $auditRepo): Closure
    {
        $recorder = new AuditRecorder($auditRepo, new FixedClock());

        return static fn (DatabaseQueryExecutorInterface $exec): AuditRecorderInterface => $recorder;
    }

    private function voidUseCase(InMemoryReceivedInvoiceRepository $repo, InMemoryAuditLogRepository $auditRepo): VoidReceivedInvoiceUseCase
    {
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $holder->set('01ORG00000000000000000001');

        return new VoidReceivedInvoiceUseCase(
            $repo,
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory($auditRepo),
            $holder,
        );
    }

    public function test_void_pending_invoice_records_audit_with_null_after(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository($this->invoice('01A', 'pending'));
        $auditRepo = new InMemoryAuditLogRepository();

        $voided = $this->voidUseCase($repo, $auditRepo)->execute('01USER', '01A');

        self::assertSame('voided', $voided->status);
        self::assertNull($repo->findById('01A')); // excluded once voided
        self::assertSame('received_invoice.voided', $auditRepo->appended[0]->action);
        self::assertNull($auditRepo->appended[0]->after);
    }

    public function test_void_rejected_when_not_pending(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository($this->invoice('01A', 'processing'));
        $auditRepo = new InMemoryAuditLogRepository();

        $this->expectException(InvoiceNotEditableException::class);
        $this->voidUseCase($repo, $auditRepo)->execute('01USER', '01A');
    }

    public function test_void_missing_invoice_throws_not_found(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository();
        $auditRepo = new InMemoryAuditLogRepository();

        $this->expectException(ReceivedInvoiceNotFoundException::class);
        $this->voidUseCase($repo, $auditRepo)->execute('01USER', 'missing');
    }
}
