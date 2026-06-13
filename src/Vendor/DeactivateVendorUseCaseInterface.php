<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

interface DeactivateVendorUseCaseInterface
{
    /** @throws VendorNotFoundException */
    public function execute(?string $actorUserId, string $id): Vendor;
}
