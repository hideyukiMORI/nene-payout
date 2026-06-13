<?php

declare(strict_types=1);

namespace NenePayout\Payment;

interface ListPaymentExecutionsUseCaseInterface
{
    public function execute(PaymentExecutionFilter $filter, int $limit, int $offset): ListPaymentExecutionsOutput;
}
