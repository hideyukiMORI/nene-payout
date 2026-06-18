<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Http\JsonRequestBodyParser;
use Nene2\Http\JsonResponseFactory;
use Nene2\Validation\ValidationError;
use Nene2\Validation\ValidationException;
use NenePayout\Payment\InitiatePaymentInput;
use NenePayout\Payment\InitiatePaymentUseCaseInterface;
use NenePayout\Payment\PaymentExecutionResponse;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceUseCaseInterface;
use NenePayout\ReceivedInvoice\ReceivedInvoiceInputMapper;
use NenePayout\ReceivedInvoice\ReceivedInvoiceResponse;
use NenePayout\Vendor\CreateVendorUseCaseInterface;
use NenePayout\Vendor\VendorInputMapper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Widget Mode A (ADR 0021): the host already has the invoice and passes the
 * payee (振込先) + amount as arguments. We **record** the vendor and received
 * invoice on the operator's own server (data ownership + audit + immutability,
 * ADR 0011/0013), then initiate the gateway-hosted card payment (no PAN,
 * ADR 0010). Reuses the same input validation and use cases as the admin flow.
 */
final readonly class InitiateWidgetQuickPaymentHandler
{
    private const ALLOWED_GATEWAYS = ['stripe', 'gmo_pg'];

    public function __construct(
        private CreateVendorUseCaseInterface $createVendor,
        private CreateReceivedInvoiceUseCaseInterface $createInvoice,
        private InitiatePaymentUseCaseInterface $initiatePayment,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = JsonRequestBodyParser::parse($request);

        $gateway = isset($body['gateway']) && is_string($body['gateway']) ? $body['gateway'] : '';
        if (!in_array($gateway, self::ALLOWED_GATEWAYS, true)) {
            throw new ValidationException([new ValidationError('gateway', 'gateway must be one of: stripe, gmo_pg.', 'invalid_value')]);
        }

        $returnUrl = isset($body['return_url']) && is_string($body['return_url']) && $body['return_url'] !== ''
            ? $body['return_url']
            : null;

        // Resolve the payee (振込先): an existing vendor id, or create one from
        // the inline bank details passed by the host.
        $vendorId = isset($body['vendor_id']) && is_string($body['vendor_id']) && $body['vendor_id'] !== ''
            ? $body['vendor_id']
            : null;

        if ($vendorId === null) {
            $vendorBody = isset($body['vendor']) && is_array($body['vendor']) ? $body['vendor'] : null;
            if ($vendorBody === null) {
                throw new ValidationException([new ValidationError('vendor', 'Either vendor_id or an inline vendor object is required.', 'required')]);
            }

            $vendor = $this->createVendor->execute(null, VendorInputMapper::create($vendorBody));
            $vendorId = (string) $vendor->id;
        }

        // Record the received invoice from the passed arguments.
        $invoiceBody = $body;
        $invoiceBody['vendor_id'] = $vendorId;
        $invoice = $this->createInvoice->execute(null, ReceivedInvoiceInputMapper::create($invoiceBody));

        // Initiate the gateway-hosted card payment.
        $output = $this->initiatePayment->execute(
            null,
            new InitiatePaymentInput((string) $invoice->id, $gateway, $returnUrl),
        );

        return $this->response->create([
            'received_invoice'     => ReceivedInvoiceResponse::toArray($invoice),
            'payment_execution'    => PaymentExecutionResponse::toArray($output->paymentExecution),
            'gateway_redirect_url' => $output->gatewayRedirectUrl,
            'client_token'         => $output->clientToken,
        ], 201);
    }
}
