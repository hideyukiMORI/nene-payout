<?php

declare(strict_types=1);

namespace NenePayout\ReceivedInvoice;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class CreateReceivedInvoiceHandler
{
    public function __construct(
        private CreateReceivedInvoiceUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = ReceivedInvoiceInputMapper::create(JsonRequestBodyParser::parse($request));

        $invoice = $this->useCase->execute(AuthContext::actorUserId($request), $input);

        return $this->response->create(ReceivedInvoiceResponse::toArray($invoice), 201);
    }
}
