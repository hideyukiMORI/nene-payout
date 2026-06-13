<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use Closure;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Audit\AuditRecorder;
use NenePayout\Audit\AuditRecorderInterface;
use NenePayout\ReceivedInvoice\AttachReceivedInvoicePdfUseCase;
use NenePayout\ReceivedInvoice\Pdf\PdfStorageInterface;
use NenePayout\ReceivedInvoice\ReceivedInvoice;
use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundException;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\Tests\Audit\InMemoryAuditLogRepository;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

final class AttachReceivedInvoicePdfUseCaseTest extends TestCase
{
    private InMemoryAuditLogRepository $auditRepo;

    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditLogRepository();
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

    /** @return Closure(DatabaseQueryExecutorInterface): AuditRecorderInterface */
    private function auditFactory(): Closure
    {
        $recorder = new AuditRecorder($this->auditRepo, new FixedClock());

        return static fn (DatabaseQueryExecutorInterface $exec): AuditRecorderInterface => $recorder;
    }

    private function storage(): PdfStorageInterface
    {
        return new class () implements PdfStorageInterface {
            public function store(string $organizationId, string $invoiceId, UploadedFileInterface $file): string
            {
                return 'received-invoices/' . $organizationId . '/' . $invoiceId . '.pdf';
            }
        };
    }

    private function uploadedFile(): UploadedFileInterface
    {
        $psr17 = new Psr17Factory();

        return $psr17->createUploadedFile($psr17->createStream('%PDF-1.7 test'), 12, UPLOAD_ERR_OK, 'invoice.pdf', 'application/pdf');
    }

    private function invoice(): ReceivedInvoice
    {
        return new ReceivedInvoice(
            vendorId: '01VENDOR000000000000000001',
            amount: 100000,
            dueDate: '2026-07-31',
            status: 'pending',
            organizationId: '01ORG00000000000000000001',
            id: '01INV00000000000000000001',
        );
    }

    public function test_attaches_pdf_and_records_audit(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository($this->invoice());
        $useCase = new AttachReceivedInvoicePdfUseCase(
            $repo,
            $this->storage(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $updated = $useCase->execute('01USER0000000000000000001', '01INV00000000000000000001', $this->uploadedFile());

        self::assertSame('received-invoices/01ORG00000000000000000001/01INV00000000000000000001.pdf', $updated->pdfPath);
        self::assertCount(1, $this->auditRepo->appended);
        self::assertSame('received_invoice.pdf_attached', $this->auditRepo->appended[0]->action);
    }

    public function test_unknown_invoice_throws(): void
    {
        $repo = new InMemoryReceivedInvoiceRepository();
        $useCase = new AttachReceivedInvoicePdfUseCase(
            $repo,
            $this->storage(),
            new ImmediateTransactionManager(),
            $this->invoicesFactory($repo),
            $this->auditFactory(),
            $this->orgId,
        );

        $this->expectException(ReceivedInvoiceNotFoundException::class);
        $useCase->execute(null, 'missing', $this->uploadedFile());
    }
}
