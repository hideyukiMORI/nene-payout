<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

interface ListVendorsUseCaseInterface
{
    public function execute(?string $nameQuery, int $limit, int $offset): ListVendorsOutput;
}
