<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final readonly class ListPaymentExecutionsUseCase implements ListPaymentExecutionsUseCaseInterface
{
    public function __construct(
        private PaymentExecutionRepositoryInterface $payments,
    ) {
    }

    public function execute(PaymentExecutionFilter $filter, int $limit, int $offset): ListPaymentExecutionsOutput
    {
        return new ListPaymentExecutionsOutput(
            items: $this->payments->findAll($filter, $limit, $offset),
            total: $this->payments->count($filter),
        );
    }
}
