<?php

declare(strict_types=1);

namespace NenePayout\Tests\ReceivedInvoice;

use NenePayout\ReceivedInvoice\ReceivedInvoice;
use NenePayout\ReceivedInvoice\ReceivedInvoiceFilter;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;

final class InMemoryReceivedInvoiceRepository implements ReceivedInvoiceRepositoryInterface
{
    /** @var list<ReceivedInvoice> */
    private array $invoices;

    public function __construct(ReceivedInvoice ...$invoices)
    {
        $this->invoices = array_values($invoices);
    }

    public function findById(string $id): ?ReceivedInvoice
    {
        foreach ($this->invoices as $invoice) {
            if ($invoice->id === $id && $invoice->status !== 'voided') {
                return $invoice;
            }
        }

        return null;
    }

    /** @return list<ReceivedInvoice> */
    public function findAll(ReceivedInvoiceFilter $filter, int $limit, int $offset): array
    {
        return array_slice($this->match($filter), $offset, $limit);
    }

    public function count(ReceivedInvoiceFilter $filter): int
    {
        return count($this->match($filter));
    }

    public function save(ReceivedInvoice $invoice): void
    {
        $this->invoices[] = $invoice;
    }

    public function update(ReceivedInvoice $invoice): void
    {
        foreach ($this->invoices as $i => $existing) {
            if ($existing->id === $invoice->id) {
                $this->invoices[$i] = $invoice;

                return;
            }
        }
    }

    public function attachPdf(string $id, string $pdfPath): void
    {
        foreach ($this->invoices as $i => $existing) {
            if ($existing->id === $id) {
                $this->invoices[$i] = new ReceivedInvoice(
                    vendorId: $existing->vendorId,
                    amount: $existing->amount,
                    dueDate: $existing->dueDate,
                    status: $existing->status,
                    taxBreakdown: $existing->taxBreakdown,
                    organizationId: $existing->organizationId,
                    registrationNumber: $existing->registrationNumber,
                    vaultDocumentUrl: $existing->vaultDocumentUrl,
                    pdfPath: $pdfPath,
                    id: $existing->id,
                    createdAt: $existing->createdAt,
                    updatedAt: $existing->updatedAt,
                );

                return;
            }
        }
    }

    /** @return list<ReceivedInvoice> */
    private function match(ReceivedInvoiceFilter $filter): array
    {
        return array_values(array_filter(
            $this->invoices,
            static function (ReceivedInvoice $invoice) use ($filter): bool {
                if ($filter->status !== null) {
                    if ($invoice->status !== $filter->status) {
                        return false;
                    }
                } elseif ($invoice->status === 'voided') {
                    return false;
                }

                if ($filter->vendorId !== null && $invoice->vendorId !== $filter->vendorId) {
                    return false;
                }

                return true;
            },
        ));
    }
}
