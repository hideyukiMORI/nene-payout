<?php

declare(strict_types=1);

namespace NenePayout\Tests\Payment;

use NenePayout\Payment\PaymentExecution;
use NenePayout\Payment\PaymentExecutionFilter;
use NenePayout\Payment\PaymentExecutionRepositoryInterface;

final class InMemoryPaymentExecutionRepository implements PaymentExecutionRepositoryInterface
{
    /** @var list<PaymentExecution> */
    public array $saved = [];

    public function __construct(PaymentExecution ...$payments)
    {
        $this->saved = array_values($payments);
    }

    public function findById(string $id): ?PaymentExecution
    {
        foreach ($this->saved as $payment) {
            if ($payment->id === $id) {
                return $payment;
            }
        }

        return null;
    }

    /** @return list<PaymentExecution> */
    public function findByReceivedInvoiceId(string $receivedInvoiceId): array
    {
        return array_values(array_filter(
            $this->saved,
            static fn (PaymentExecution $p): bool => $p->receivedInvoiceId === $receivedInvoiceId,
        ));
    }

    /** @return list<PaymentExecution> */
    public function findAll(PaymentExecutionFilter $filter, int $limit, int $offset): array
    {
        return array_slice($this->match($filter), $offset, $limit);
    }

    public function count(PaymentExecutionFilter $filter): int
    {
        return count($this->match($filter));
    }

    public function save(PaymentExecution $payment): void
    {
        $this->saved[] = $payment;
    }

    /** @return list<PaymentExecution> */
    private function match(PaymentExecutionFilter $filter): array
    {
        return array_values(array_filter(
            $this->saved,
            static function (PaymentExecution $p) use ($filter): bool {
                if ($filter->status !== null && $p->status !== $filter->status) {
                    return false;
                }

                if ($filter->receivedInvoiceId !== null && $p->receivedInvoiceId !== $filter->receivedInvoiceId) {
                    return false;
                }

                return true;
            },
        ));
    }
}
