<?php

declare(strict_types=1);

namespace NenePayout\Payment;

final readonly class GetPaymentExecutionUseCase implements GetPaymentExecutionUseCaseInterface
{
    public function __construct(
        private PaymentExecutionRepositoryInterface $payments,
    ) {
    }

    public function execute(string $id): PaymentExecution
    {
        $payment = $this->payments->findById($id);

        if ($payment === null) {
            throw new PaymentExecutionNotFoundException($id);
        }

        return $payment;
    }
}
