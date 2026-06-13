<?php

declare(strict_types=1);

namespace NenePayout\Payment;

/**
 * Tenant-scoped payment-execution persistence (org forced from the request-scoped
 * holder). Terminal records are immutable; later events are new linked records
 * (ADR 0013) — added with the webhook slice.
 */
interface PaymentExecutionRepositoryInterface
{
    public function findById(string $id): ?PaymentExecution;

    /** @return list<PaymentExecution> */
    public function findByReceivedInvoiceId(string $receivedInvoiceId): array;

    /** @return list<PaymentExecution> */
    public function findAll(PaymentExecutionFilter $filter, int $limit, int $offset): array;

    public function count(PaymentExecutionFilter $filter): int;

    public function save(PaymentExecution $payment): void;
}
