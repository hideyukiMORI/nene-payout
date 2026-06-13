<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

/**
 * Tenant-scoped received-invoice persistence. Implementations force the
 * organization from the request-scoped holder. `findById` excludes voided
 * invoices (ADR 0013, 0018).
 */
interface ReceivedInvoiceRepositoryInterface
{
    public function findById(string $id): ?ReceivedInvoice;

    /** @return list<ReceivedInvoice> */
    public function findAll(ReceivedInvoiceFilter $filter, int $limit, int $offset): array;

    public function count(ReceivedInvoiceFilter $filter): int;

    public function save(ReceivedInvoice $invoice): void;

    public function update(ReceivedInvoice $invoice): void;
}
