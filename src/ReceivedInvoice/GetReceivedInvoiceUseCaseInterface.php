<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

interface GetReceivedInvoiceUseCaseInterface
{
    /** @throws ReceivedInvoiceNotFoundException */
    public function execute(string $id): ReceivedInvoice;
}
