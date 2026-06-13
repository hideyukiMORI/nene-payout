<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

interface GetVendorUseCaseInterface
{
    /** @throws VendorNotFoundException */
    public function execute(string $id): Vendor;
}
