<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetReceivedInvoiceHandler
{
    public function __construct(
        private GetReceivedInvoiceUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['received_invoice_id']) && is_string($params['received_invoice_id'])
            ? $params['received_invoice_id']
            : '';

        $invoice = $this->useCase->execute($id);

        // payment_executions is populated once the payment slice lands.
        return $this->response->create(ReceivedInvoiceResponse::toDetailArray($invoice));
    }
}
