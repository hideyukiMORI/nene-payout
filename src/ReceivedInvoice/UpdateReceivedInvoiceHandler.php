<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class UpdateReceivedInvoiceHandler
{
    public function __construct(
        private UpdateReceivedInvoiceUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['received_invoice_id']) && is_string($params['received_invoice_id'])
            ? $params['received_invoice_id']
            : '';

        $input = ReceivedInvoiceInputMapper::update(JsonRequestBodyParser::parse($request));

        $invoice = $this->useCase->execute(AuthContext::actorUserId($request), $id, $input);

        return $this->response->create(ReceivedInvoiceResponse::toArray($invoice));
    }
}
