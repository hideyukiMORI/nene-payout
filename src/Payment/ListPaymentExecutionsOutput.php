<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final readonly class ListPaymentExecutionsOutput
{
    /**
     * @param list<PaymentExecution> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
