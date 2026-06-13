<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

use RuntimeException;

final class VendorNotFoundException extends RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Vendor %s was not found.', $id));
    }
}
