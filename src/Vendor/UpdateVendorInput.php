<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

final readonly class UpdateVendorInput
{
    public function __construct(
        public string $name,
        public string $bankCode,
        public string $branchCode,
        public string $accountType,
        public string $accountNumber,
        public string $accountName,
        public ?string $registrationNumber = null,
    ) {
    }
}
