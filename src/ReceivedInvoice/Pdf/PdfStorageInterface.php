<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice\Pdf;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Stores an uploaded received-invoice PDF and returns the stored path (kept on
 * the operator's own server; not served via the API — domain-model.md).
 */
interface PdfStorageInterface
{
    public function store(string $organizationId, string $invoiceId, UploadedFileInterface $file): string;
}
