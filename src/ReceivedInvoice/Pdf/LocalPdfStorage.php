<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice\Pdf;

use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * Stores PDFs on the local filesystem under a base directory (default
 * `<project>/var/storage`). Returns a relative path. All data stays on the
 * operator's own server (product-vision).
 */
final readonly class LocalPdfStorage implements PdfStorageInterface
{
    public function __construct(
        private string $baseDir,
    ) {
    }

    public function store(string $organizationId, string $invoiceId, UploadedFileInterface $file): string
    {
        $relativeDir = 'received-invoices/' . $organizationId;
        $absoluteDir = $this->baseDir . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0o775, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Could not create PDF storage directory.');
        }

        $relativePath = $relativeDir . '/' . $invoiceId . '.pdf';
        $file->moveTo($this->baseDir . '/' . $relativePath);

        return $relativePath;
    }
}
