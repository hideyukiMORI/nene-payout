<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use RuntimeException;

final class ReceivedInvoiceNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Received invoice %s was not found.', $id));
    }
}
