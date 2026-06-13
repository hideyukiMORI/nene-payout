<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

interface UpdateReceivedInvoiceUseCaseInterface
{
    /**
     * @throws ReceivedInvoiceNotFoundException
     * @throws InvoiceNotEditableException
     */
    public function execute(?string $actorUserId, string $id, UpdateReceivedInvoiceInput $input): ReceivedInvoice;
}
