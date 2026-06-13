<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class GetPaymentExecutionHandler
{
    public function __construct(
        private GetPaymentExecutionUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $id = is_array($params) && isset($params['payment_execution_id']) && is_string($params['payment_execution_id'])
            ? $params['payment_execution_id']
            : '';

        return $this->response->create(PaymentExecutionResponse::toArray($this->useCase->execute($id)));
    }
}
