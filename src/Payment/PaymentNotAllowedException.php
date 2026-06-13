<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use RuntimeException;

/** Thrown when a payment is initiated for an invoice not in a payable state. */
final class PaymentNotAllowedException extends RuntimeException
{
    public function __construct(string $status)
    {
        parent::__construct(sprintf('Invoice is not payable in status "%s".', $status));
    }
}
