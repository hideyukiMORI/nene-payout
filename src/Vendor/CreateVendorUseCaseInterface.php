<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

interface CreateVendorUseCaseInterface
{
    public function execute(?string $actorUserId, CreateVendorInput $input): Vendor;
}
