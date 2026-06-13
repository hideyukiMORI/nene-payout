<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;

final readonly class PdoReceivedInvoiceRepository implements ReceivedInvoiceRepositoryInterface
{
    private const COLUMNS = 'id, organization_id, vendor_id, amount, due_date, status, registration_number, tax_breakdown, vault_document_url, created_at, updated_at';

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function findById(string $id): ?ReceivedInvoice
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . " FROM received_invoices WHERE id = ? AND organization_id = ? AND status != 'voided'",
            [$id, $this->orgId->get()],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<ReceivedInvoice> */
    public function findAll(ReceivedInvoiceFilter $filter, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($filter);

        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM received_invoices WHERE ' . $where . ' ORDER BY due_date ASC, id DESC LIMIT ? OFFSET ?',
            [...$params, $limit, $offset],
        );

        return array_map(fn (array $row): ReceivedInvoice => $this->mapRow($row), $rows);
    }

    public function count(ReceivedInvoiceFilter $filter): int
    {
        [$where, $params] = $this->where($filter);

        $row = $this->query->fetchOne('SELECT COUNT(*) AS cnt FROM received_invoices WHERE ' . $where, $params);

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    public function save(ReceivedInvoice $invoice): void
    {
        $now = date('Y-m-d H:i:s');

        $this->query->execute(
            'INSERT INTO received_invoices
                (id, organization_id, vendor_id, amount, due_date, status, registration_number, tax_breakdown, vault_document_url, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $invoice->id,
                $this->orgId->get(),
                $invoice->vendorId,
                $invoice->amount,
                $invoice->dueDate,
                $invoice->status,
                $invoice->registrationNumber,
                json_encode($invoice->taxBreakdown, JSON_THROW_ON_ERROR),
                $invoice->vaultDocumentUrl,
                $now,
                $now,
            ],
        );
    }

    public function update(ReceivedInvoice $invoice): void
    {
        $this->query->execute(
            'UPDATE received_invoices
             SET vendor_id = ?, amount = ?, due_date = ?, status = ?, registration_number = ?, tax_breakdown = ?, vault_document_url = ?, updated_at = ?
             WHERE id = ? AND organization_id = ?',
            [
                $invoice->vendorId,
                $invoice->amount,
                $invoice->dueDate,
                $invoice->status,
                $invoice->registrationNumber,
                json_encode($invoice->taxBreakdown, JSON_THROW_ON_ERROR),
                $invoice->vaultDocumentUrl,
                date('Y-m-d H:i:s'),
                $invoice->id,
                $this->orgId->get(),
            ],
        );
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function where(ReceivedInvoiceFilter $filter): array
    {
        $clauses = ['organization_id = ?'];
        $params = [$this->orgId->get()];

        if ($filter->status !== null) {
            $clauses[] = 'status = ?';
            $params[] = $filter->status;
        } else {
            $clauses[] = "status != 'voided'";
        }

        if ($filter->vendorId !== null) {
            $clauses[] = 'vendor_id = ?';
            $params[] = $filter->vendorId;
        }

        if ($filter->dueFrom !== null) {
            $clauses[] = 'due_date >= ?';
            $params[] = $filter->dueFrom;
        }

        if ($filter->dueTo !== null) {
            $clauses[] = 'due_date <= ?';
            $params[] = $filter->dueTo;
        }

        return [implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): ReceivedInvoice
    {
        return new ReceivedInvoice(
            vendorId: (string) $row['vendor_id'],
            amount: (int) $row['amount'],
            dueDate: (string) $row['due_date'],
            status: (string) $row['status'],
            taxBreakdown: self::decodeBreakdown($row['tax_breakdown'] ?? null),
            organizationId: (string) $row['organization_id'],
            registrationNumber: $row['registration_number'] !== null ? (string) $row['registration_number'] : null,
            vaultDocumentUrl: $row['vault_document_url'] !== null ? (string) $row['vault_document_url'] : null,
            id: (string) $row['id'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    /**
     * @return list<array{tax_rate_bps: int, taxable_amount: int, tax_amount: int}>
     */
    private static function decodeBreakdown(mixed $json): array
    {
        if (!is_string($json) || $json === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            return [];
        }

        $items = [];
        foreach ($decoded as $item) {
            if (is_array($item) && isset($item['tax_rate_bps'], $item['taxable_amount'], $item['tax_amount'])) {
                $items[] = [
                    'tax_rate_bps' => (int) $item['tax_rate_bps'],
                    'taxable_amount' => (int) $item['taxable_amount'],
                    'tax_amount' => (int) $item['tax_amount'],
                ];
            }
        }

        return $items;
    }
}
