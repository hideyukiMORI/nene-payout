<?php

declare(strict_types=1);

namespace NenePayout\Vendor;

final readonly class ListVendorsOutput
{
    /**
     * @param list<Vendor> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
