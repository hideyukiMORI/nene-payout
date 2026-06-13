<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Nene2\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PaymentRouteRegistrar
{
    public function __construct(
        private InitiatePaymentHandler $initiateHandler,
        private ListPaymentExecutionsHandler $listHandler,
        private GetPaymentExecutionHandler $getHandler,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $initiate = $this->initiateHandler;
        $list = $this->listHandler;
        $get = $this->getHandler;

        $router->post('/api/v1/received-invoices/{received_invoice_id}/payments', static fn (ServerRequestInterface $r) => $initiate->handle($r));
        $router->get('/api/v1/payment-executions', static fn (ServerRequestInterface $r) => $list->handle($r));
        $router->get('/api/v1/payment-executions/{payment_execution_id}', static fn (ServerRequestInterface $r) => $get->handle($r));
    }
}
