<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundException;

interface InitiatePaymentUseCaseInterface
{
    /**
     * @throws ReceivedInvoiceNotFoundException
     * @throws PaymentNotAllowedException
     */
    public function execute(?string $actorUserId, InitiatePaymentInput $input): InitiatePaymentOutput;
}
