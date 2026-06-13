<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

interface UpdateVendorUseCaseInterface
{
    /** @throws VendorNotFoundException */
    public function execute(?string $actorUserId, string $id, UpdateVendorInput $input): Vendor;
}
