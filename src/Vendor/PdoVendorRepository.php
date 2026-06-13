<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;

final readonly class PdoVendorRepository implements VendorRepositoryInterface
{
    private const COLUMNS = 'id, organization_id, name, bank_code, branch_code, account_type, account_number, account_name, registration_number, is_active, created_at, updated_at';

    /**
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private DatabaseQueryExecutorInterface $query,
        private RequestScopedHolder $orgId,
    ) {
    }

    public function findById(string $id): ?Vendor
    {
        $row = $this->query->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM vendors WHERE id = ? AND organization_id = ? AND is_active = 1',
            [$id, $this->orgId->get()],
        );

        return $row !== null ? $this->mapRow($row) : null;
    }

    /** @return list<Vendor> */
    public function findAll(?string $nameQuery, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($nameQuery);

        $rows = $this->query->fetchAll(
            'SELECT ' . self::COLUMNS . ' FROM vendors WHERE ' . $where . ' ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?',
            [...$params, $limit, $offset],
        );

        return array_map(fn (array $row): Vendor => $this->mapRow($row), $rows);
    }

    public function count(?string $nameQuery): int
    {
        [$where, $params] = $this->where($nameQuery);

        $row = $this->query->fetchOne('SELECT COUNT(*) AS cnt FROM vendors WHERE ' . $where, $params);

        return $row !== null ? (int) $row['cnt'] : 0;
    }

    public function save(Vendor $vendor): void
    {
        $now = date('Y-m-d H:i:s');

        $this->query->execute(
            'INSERT INTO vendors
                (id, organization_id, name, bank_code, branch_code, account_type, account_number, account_name, registration_number, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $vendor->id,
                $this->orgId->get(),
                $vendor->name,
                $vendor->bankCode,
                $vendor->branchCode,
                $vendor->accountType,
                $vendor->accountNumber,
                $vendor->accountName,
                $vendor->registrationNumber,
                $vendor->isActive ? 1 : 0,
                $now,
                $now,
            ],
        );
    }

    public function update(Vendor $vendor): void
    {
        $this->query->execute(
            'UPDATE vendors
             SET name = ?, bank_code = ?, branch_code = ?, account_type = ?, account_number = ?, account_name = ?, registration_number = ?, is_active = ?, updated_at = ?
             WHERE id = ? AND organization_id = ?',
            [
                $vendor->name,
                $vendor->bankCode,
                $vendor->branchCode,
                $vendor->accountType,
                $vendor->accountNumber,
                $vendor->accountName,
                $vendor->registrationNumber,
                $vendor->isActive ? 1 : 0,
                date('Y-m-d H:i:s'),
                $vendor->id,
                $this->orgId->get(),
            ],
        );
    }

    /**
     * @return array{0: string, 1: list<string>}
     */
    private function where(?string $nameQuery): array
    {
        $clauses = ['organization_id = ?', 'is_active = 1'];
        $params = [$this->orgId->get()];

        if ($nameQuery !== null && $nameQuery !== '') {
            $clauses[] = 'name LIKE ?';
            $params[] = '%' . $nameQuery . '%';
        }

        return [implode(' AND ', $clauses), $params];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): Vendor
    {
        return new Vendor(
            name: (string) $row['name'],
            bankCode: (string) $row['bank_code'],
            branchCode: (string) $row['branch_code'],
            accountType: (string) $row['account_type'],
            accountNumber: (string) $row['account_number'],
            accountName: (string) $row['account_name'],
            isActive: (bool) (int) $row['is_active'],
            organizationId: (string) $row['organization_id'],
            registrationNumber: $row['registration_number'] !== null ? (string) $row['registration_number'] : null,
            id: (string) $row['id'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }
}
