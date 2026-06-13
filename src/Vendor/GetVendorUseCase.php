<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

final readonly class GetVendorUseCase implements GetVendorUseCaseInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendors,
    ) {
    }

    public function execute(string $id): Vendor
    {
        $vendor = $this->vendors->findById($id);

        if ($vendor === null) {
            throw new VendorNotFoundException($id);
        }

        return $vendor;
    }
}
