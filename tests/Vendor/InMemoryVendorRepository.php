<?php

declare(strict_types=1);

namespace NenePayout\Tests\Vendor;

use NenePayout\Vendor\Vendor;
use NenePayout\Vendor\VendorRepositoryInterface;

final class InMemoryVendorRepository implements VendorRepositoryInterface
{
    /** @var list<Vendor> */
    private array $vendors;

    public function __construct(Vendor ...$vendors)
    {
        $this->vendors = array_values($vendors);
    }

    public function findById(string $id): ?Vendor
    {
        foreach ($this->vendors as $vendor) {
            if ($vendor->id === $id && $vendor->isActive) {
                return $vendor;
            }
        }

        return null;
    }

    /** @return list<Vendor> */
    public function findAll(?string $nameQuery, int $limit, int $offset): array
    {
        return array_slice($this->match($nameQuery), $offset, $limit);
    }

    public function count(?string $nameQuery): int
    {
        return count($this->match($nameQuery));
    }

    public function save(Vendor $vendor): void
    {
        $this->vendors[] = $vendor;
    }

    public function update(Vendor $vendor): void
    {
        foreach ($this->vendors as $i => $existing) {
            if ($existing->id === $vendor->id) {
                $this->vendors[$i] = $vendor;

                return;
            }
        }
    }

    /** @return list<Vendor> */
    private function match(?string $nameQuery): array
    {
        return array_values(array_filter(
            $this->vendors,
            static function (Vendor $vendor) use ($nameQuery): bool {
                if (!$vendor->isActive) {
                    return false;
                }

                return $nameQuery === null || str_contains($vendor->name, $nameQuery);
            },
        ));
    }
}
