<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

final readonly class GetReceivedInvoiceUseCase implements GetReceivedInvoiceUseCaseInterface
{
    public function __construct(
        private ReceivedInvoiceRepositoryInterface $invoices,
    ) {
    }

    public function execute(string $id): ReceivedInvoice
    {
        $invoice = $this->invoices->findById($id);

        if ($invoice === null) {
            throw new ReceivedInvoiceNotFoundException($id);
        }

        return $invoice;
    }
}
