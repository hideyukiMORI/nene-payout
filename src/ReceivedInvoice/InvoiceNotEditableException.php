<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use RuntimeException;

/** Thrown when editing/voiding is attempted in a non-permitted status. */
final class InvoiceNotEditableException extends RuntimeException
{
    public function __construct(string $status)
    {
        parent::__construct(sprintf('Received invoice is not editable in status "%s".', $status));
    }
}
