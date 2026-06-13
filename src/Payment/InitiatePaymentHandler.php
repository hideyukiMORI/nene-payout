<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Support\AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class InitiatePaymentHandler
{
    private const ALLOWED_GATEWAYS = ['stripe', 'gmo_pg'];

    public function __construct(
        private InitiatePaymentUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
        $invoiceId = is_array($params) && isset($params['received_invoice_id']) && is_string($params['received_invoice_id'])
            ? $params['received_invoice_id']
            : '';

        $body = JsonRequestBodyParser::parse($request);

        $gateway = isset($body['gateway']) && is_string($body['gateway']) ? $body['gateway'] : '';
        if (!in_array($gateway, self::ALLOWED_GATEWAYS, true)) {
            throw new ValidationException([new ValidationError('gateway', 'gateway must be one of: stripe, gmo_pg.', 'invalid_value')]);
        }

        $returnUrl = isset($body['return_url']) && is_string($body['return_url']) && $body['return_url'] !== ''
            ? $body['return_url']
            : null;

        $output = $this->useCase->execute(
            AuthContext::actorUserId($request),
            new InitiatePaymentInput($invoiceId, $gateway, $returnUrl),
        );

        return $this->response->create([
            'payment_execution'    => PaymentExecutionResponse::toArray($output->paymentExecution),
            'gateway_redirect_url' => $output->gatewayRedirectUrl,
            'client_token'         => $output->clientToken,
        ], 201);
    }
}
