<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationException;
use NenePayout\ReceivedInvoice\AttachReceivedInvoicePdfHandler;
use NenePayout\ReceivedInvoice\AttachReceivedInvoicePdfUseCaseInterface;
use NenePayout\ReceivedInvoice\ReceivedInvoice;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/** Fake that returns a fixed invoice and records whether it was called. */
final class FakeAttachPdfUseCase implements AttachReceivedInvoicePdfUseCaseInterface
{
    public bool $called = false;

    public function execute(?string $actorUserId, string $id, UploadedFileInterface $file): ReceivedInvoice
    {
        $this->called = true;

        return new ReceivedInvoice(
            vendorId: '01V',
            amount: 100000,
            dueDate: '2026-07-31',
            status: 'pending',
            organizationId: '01ORG',
            id: $id,
        );
    }
}

final class AttachReceivedInvoicePdfHandlerTest extends TestCase
{
    private Psr17Factory $psr17;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
    }

    /**
     * @param array<string, UploadedFileInterface> $files
     */
    private function request(array $files): ServerRequestInterface
    {
        return $this->psr17->createServerRequest('POST', 'https://example.com/api/v1/received-invoices/01I/pdf')
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['received_invoice_id' => '01I'])
            ->withUploadedFiles($files);
    }

    private function uploadedPdf(int $error = UPLOAD_ERR_OK, string $mediaType = 'application/pdf'): UploadedFileInterface
    {
        return $this->psr17->createUploadedFile($this->psr17->createStream('%PDF-1.7'), 8, $error, 'invoice.pdf', $mediaType);
    }

    private function handler(FakeAttachPdfUseCase $useCase): AttachReceivedInvoicePdfHandler
    {
        return new AttachReceivedInvoicePdfHandler($useCase, new JsonResponseFactory($this->psr17, $this->psr17));
    }

    public function test_valid_pdf_returns_200_and_calls_use_case(): void
    {
        $useCase = new FakeAttachPdfUseCase();
        $response = $this->handler($useCase)->handle($this->request(['file' => $this->uploadedPdf()]));

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($useCase->called);
    }

    public function test_missing_file_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler(new FakeAttachPdfUseCase())->handle($this->request([]));
    }

    public function test_non_pdf_media_type_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler(new FakeAttachPdfUseCase())->handle($this->request(['file' => $this->uploadedPdf(mediaType: 'image/png')]));
    }

    public function test_upload_error_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler(new FakeAttachPdfUseCase())->handle($this->request(['file' => $this->uploadedPdf(error: UPLOAD_ERR_INI_SIZE)]));
    }
}
