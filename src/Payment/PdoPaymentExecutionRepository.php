<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;

final readonly class PdoPaymentExecutionRepository implements PaymentExecutionRepositoryInterface
{
    private const COLUMNS = 'id, organization_id, received_invoice_id, amount, charge_amount, processing_fee, gateway, gateway_reference, status, initiated_at, completed_at';

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function findById(string $id): ?PaymentExecution
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM payment_executions WHERE id = ? AND organization_id = ?',
            [$id, $this->orgId->get()],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<PaymentExecution> */
    public function findByReceivedInvoiceId(string $receivedInvoiceId): array
    {
        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM payment_executions WHERE organization_id = ? AND received_invoice_id = ? ORDER BY initiated_at DESC, id DESC',
            [$this->orgId->get(), $receivedInvoiceId],
        );

        return array_map(fn (array $row): PaymentExecution => $this->mapRow($row), $rows);
    }

    /** @return list<PaymentExecution> */
    public function findAll(PaymentExecutionFilter $filter, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($filter);

        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM payment_executions WHERE ' . $where . ' ORDER BY initiated_at DESC, id DESC LIMIT ? OFFSET ?',
            [...$params, $limit, $offset],
        );

        return array_map(fn (array $row): PaymentExecution => $this->mapRow($row), $rows);
    }

    public function count(PaymentExecutionFilter $filter): int
    {
        [$where, $params] = $this->where($filter);

        $row = $this->query->fetchOne('SELECT COUNT(*) AS cnt FROM payment_executions WHERE ' . $where, $params);

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    public function save(PaymentExecution $payment): void
    {
        $this->query->execute(
            'INSERT INTO payment_executions
                (id, organization_id, received_invoice_id, amount, charge_amount, processing_fee, gateway, gateway_reference, status, initiated_at, completed_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $payment->id,
                $this->orgId->get(),
                $payment->receivedInvoiceId,
                $payment->amount,
                $payment->chargeAmount,
                $payment->processingFee,
                $payment->gateway,
                $payment->gatewayReference,
                $payment->status,
                $payment->initiatedAt,
                $payment->completedAt,
            ],
        );
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function where(PaymentExecutionFilter $filter): array
    {
        $clauses = ['organization_id = ?'];
        $params = [$this->orgId->get()];

        if ($filter->status !== null) {
            $clauses[] = 'status = ?';
            $params[] = $filter->status;
        }

        if ($filter->receivedInvoiceId !== null) {
            $clauses[] = 'received_invoice_id = ?';
            $params[] = $filter->receivedInvoiceId;
        }

        return [implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): PaymentExecution
    {
        return new PaymentExecution(
            receivedInvoiceId: (string) $row['received_invoice_id'],
            amount: (int) $row['amount'],
            gateway: (string) $row['gateway'],
            status: (string) $row['status'],
            organizationId: (string) $row['organization_id'],
            chargeAmount: $row['charge_amount'] !== null ? (int) $row['charge_amount'] : null,
            processingFee: $row['processing_fee'] !== null ? (int) $row['processing_fee'] : null,
            gatewayReference: $row['gateway_reference'] !== null ? (string) $row['gateway_reference'] : null,
            id: (string) $row['id'],
            initiatedAt: (string) $row['initiated_at'],
            completedAt: $row['completed_at'] !== null ? (string) $row['completed_at'] : null,
        );
    }
}
