<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

/**
 * Tenant-scoped vendor persistence. Implementations force the organization from
 * the request-scoped holder and operate only on active vendors (ADR 0013, 0018).
 */
interface VendorRepositoryInterface
{
    public function findById(string $id): ?Vendor;

    /** @return list<Vendor> */
    public function findAll(?string $nameQuery, int $limit, int $offset): array;

    public function count(?string $nameQuery): int;

    public function save(Vendor $vendor): void;

    public function update(Vendor $vendor): void;
}
