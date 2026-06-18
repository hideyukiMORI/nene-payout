<?php

declare(strict_types=1);

namespace NenePayout\Widget;

use Nene2\Routing\Router;
use NenePayout\Payment\GetPaymentExecutionHandler;
use NenePayout\Payment\InitiatePaymentHandler;
use NenePayout\Payment\ListPaymentExecutionsHandler;
use NenePayout\ReceivedInvoice\AttachReceivedInvoicePdfHandler;
use NenePayout\ReceivedInvoice\CreateReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\GetReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\ListReceivedInvoicesHandler;
use NenePayout\ReceivedInvoice\UpdateReceivedInvoiceHandler;
use NenePayout\ReceivedInvoice\VoidReceivedInvoiceHandler;
use NenePayout\Vendor\CreateVendorHandler;
use NenePayout\Vendor\DeactivateVendorHandler;
use NenePayout\Vendor\GetVendorHandler;
use NenePayout\Vendor\ListVendorsHandler;
use NenePayout\Vendor\UpdateVendorHandler;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Routes for the embeddable widget (ADR 0021).
 *
 * - `POST /api/v1/widget-tokens` — protected token generation (normal auth+org+
 *   capability pipeline; NOT under the `/api/v1/widget/` bypass prefix).
 * - `/api/v1/widget/*` — token-gated runtime, authenticated by
 *   {@see WidgetAuthMiddleware}. Mode A (`/quick-payments`) and the Mode B
 *   management surface reuse the existing admin handlers/use cases unchanged;
 *   the widget's permission surface is exactly the route set exposed here.
 */
final readonly class WidgetRouteRegistrar
{
    public function __construct(
        private GenerateWidgetTokenHandler $generateToken,
        private GetWidgetContextHandler $context,
        private InitiateWidgetQuickPaymentHandler $quickPayment,
        private ListReceivedInvoicesHandler $listInvoices,
        private GetReceivedInvoiceHandler $getInvoice,
        private CreateReceivedInvoiceHandler $createInvoice,
        private UpdateReceivedInvoiceHandler $updateInvoice,
        private VoidReceivedInvoiceHandler $voidInvoice,
        private AttachReceivedInvoicePdfHandler $attachPdf,
        private InitiatePaymentHandler $initiatePayment,
        private ListVendorsHandler $listVendors,
        private GetVendorHandler $getVendor,
        private CreateVendorHandler $createVendor,
        private UpdateVendorHandler $updateVendor,
        private DeactivateVendorHandler $deactivateVendor,
        private ListPaymentExecutionsHandler $listPayments,
        private GetPaymentExecutionHandler $getPayment,
    ) {
    }

    public function __invoke(Router $router): void
    {
        $generate = $this->generateToken;
        $router->post('/api/v1/widget-tokens', static fn (ServerRequestInterface $r) => $generate->handle($r));

        $context = $this->context;
        $quickPay = $this->quickPayment;
        $router->get('/api/v1/widget/context', static fn (ServerRequestInterface $r) => $context->handle($r));
        $router->post('/api/v1/widget/quick-payments', static fn (ServerRequestInterface $r) => $quickPay->handle($r));

        $listInvoices = $this->listInvoices;
        $getInvoice = $this->getInvoice;
        $createInvoice = $this->createInvoice;
        $updateInvoice = $this->updateInvoice;
        $voidInvoice = $this->voidInvoice;
        $attachPdf = $this->attachPdf;
        $initiatePayment = $this->initiatePayment;
        $router->get('/api/v1/widget/received-invoices', static fn (ServerRequestInterface $r) => $listInvoices->handle($r));
        $router->post('/api/v1/widget/received-invoices', static fn (ServerRequestInterface $r) => $createInvoice->handle($r));
        $router->get('/api/v1/widget/received-invoices/{received_invoice_id}', static fn (ServerRequestInterface $r) => $getInvoice->handle($r));
        $router->patch('/api/v1/widget/received-invoices/{received_invoice_id}', static fn (ServerRequestInterface $r) => $updateInvoice->handle($r));
        $router->post('/api/v1/widget/received-invoices/{received_invoice_id}/void', static fn (ServerRequestInterface $r) => $voidInvoice->handle($r));
        $router->post('/api/v1/widget/received-invoices/{received_invoice_id}/pdf', static fn (ServerRequestInterface $r) => $attachPdf->handle($r));
        $router->post('/api/v1/widget/received-invoices/{received_invoice_id}/payments', static fn (ServerRequestInterface $r) => $initiatePayment->handle($r));

        $listVendors = $this->listVendors;
        $getVendor = $this->getVendor;
        $createVendor = $this->createVendor;
        $updateVendor = $this->updateVendor;
        $deactivateVendor = $this->deactivateVendor;
        $router->get('/api/v1/widget/vendors', static fn (ServerRequestInterface $r) => $listVendors->handle($r));
        $router->post('/api/v1/widget/vendors', static fn (ServerRequestInterface $r) => $createVendor->handle($r));
        $router->get('/api/v1/widget/vendors/{vendor_id}', static fn (ServerRequestInterface $r) => $getVendor->handle($r));
        $router->patch('/api/v1/widget/vendors/{vendor_id}', static fn (ServerRequestInterface $r) => $updateVendor->handle($r));
        $router->post('/api/v1/widget/vendors/{vendor_id}/deactivate', static fn (ServerRequestInterface $r) => $deactivateVendor->handle($r));

        $listPayments = $this->listPayments;
        $getPayment = $this->getPayment;
        $router->get('/api/v1/widget/payment-executions', static fn (ServerRequestInterface $r) => $listPayments->handle($r));
        $router->get('/api/v1/widget/payment-executions/{payment_execution_id}', static fn (ServerRequestInterface $r) => $getPayment->handle($r));
    }
}
