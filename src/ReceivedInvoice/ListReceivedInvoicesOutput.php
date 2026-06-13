<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

final readonly class ListReceivedInvoicesOutput
{
    /**
     * @param list<ReceivedInvoice> $items
     */
    public function __construct(
        public array $items,
        public int $total,
    ) {
    }
}
