<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use RuntimeException;

final class PaymentExecutionNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Payment execution %s was not found.', $id));
    }
}
