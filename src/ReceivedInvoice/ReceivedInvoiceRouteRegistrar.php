<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ReceivedInvoiceRouteRegistrar
{
    public function __construct(
        private ListReceivedInvoicesHandler $listHandler,
        private GetReceivedInvoiceHandler $getHandler,
        private CreateReceivedInvoiceHandler $createHandler,
        private UpdateReceivedInvoiceHandler $updateHandler,
        private VoidReceivedInvoiceHandler $voidHandler,
        private AttachReceivedInvoicePdfHandler $pdfHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $list = $this->listHandler;
        $get = $this->getHandler;
        $create = $this->createHandler;
        $update = $this->updateHandler;
        $void = $this->voidHandler;
        $pdf = $this->pdfHandler;

        $router->get('/api/v1/received-invoices', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->post('/api/v1/received-invoices', static fn (ServerRequestInterface $r) => $create->handle($r));
        $router->get('/api/v1/received-invoices/{received_invoice_id}', static fn (ServerRequestInterface $r) => $get->handle($r));
        $router->patch('/api/v1/received-invoices/{received_invoice_id}', static fn (ServerRequestInterface $r) => $update->handle($r));
        $router->post('/api/v1/received-invoices/{received_invoice_id}/void', static fn (ServerRequestInterface $r) => $void->handle($r));
        $router->post('/api/v1/received-invoices/{received_invoice_id}/pdf', static fn (ServerRequestInterface $r) => $pdf->handle($r));
    }
}
