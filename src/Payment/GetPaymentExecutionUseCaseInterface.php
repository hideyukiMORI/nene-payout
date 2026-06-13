<?php

declare(strict_types=1);

namespace NenePayout\Payment;

interface GetPaymentExecutionUseCaseInterface
{
    /** @throws PaymentExecutionNotFoundException */
    public function execute(string $id): PaymentExecution;
}
