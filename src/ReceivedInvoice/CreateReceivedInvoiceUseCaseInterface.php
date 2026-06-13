<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

interface CreateReceivedInvoiceUseCaseInterface
{
    public function execute(?string $actorUserId, CreateReceivedInvoiceInput $input): ReceivedInvoice;
}
