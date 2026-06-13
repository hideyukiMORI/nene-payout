<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

final readonly class ListVendorsUseCase implements ListVendorsUseCaseInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendors,
    ) {
    }

    public function execute(?string $nameQuery, int $limit, int $offset): ListVendorsOutput
    {
        return new ListVendorsOutput(
            items: $this->vendors->findAll($nameQuery, $limit, $offset),
            total: $this->vendors->count($nameQuery),
        );
    }
}
