<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Psr\Http\Message\UploadedFileInterface;

interface AttachReceivedInvoicePdfUseCaseInterface
{
    /** @throws ReceivedInvoiceNotFoundException */
    public function execute(?string $actorUserId, string $id, UploadedFileInterface $file): ReceivedInvoice;
}
