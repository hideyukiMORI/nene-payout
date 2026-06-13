<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

final readonly class ListReceivedInvoicesUseCase implements ListReceivedInvoicesUseCaseInterface
{
    public function __construct(
        private ReceivedInvoiceRepositoryInterface $invoices,
    ) {
    }

    public function execute(ReceivedInvoiceFilter $filter, int $limit, int $offset): ListReceivedInvoicesOutput
    {
        return new ListReceivedInvoicesOutput(
            items: $this->invoices->findAll($filter, $limit, $offset),
            total: $this->invoices->count($filter),
        );
    }
}
