<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

interface ListReceivedInvoicesUseCaseInterface
{
    public function execute(ReceivedInvoiceFilter $filter, int $limit, int $offset): ListReceivedInvoicesOutput;
}
