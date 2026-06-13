<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

interface VoidReceivedInvoiceUseCaseInterface
{
    /** @throws ReceivedInvoiceNotFoundException */
    public function execute(?string $actorUserId, string $id): ReceivedInvoice;
}
