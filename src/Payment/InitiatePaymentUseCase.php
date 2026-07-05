<?php

declare(strict_types=1);

namespace NenePayout\Payment;

use Closure;
use Nene2\Audit\AuditEvent;
use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Database\DatabaseTransactionManagerInterface;
use Nene2\Http\ClockInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Payment\Gateway\ChargeRequest;
use NenePayout\Payment\Gateway\PaymentGatewayInterface;
use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundException;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\ReceivedInvoice\ReceivedInvoiceStatus;
use NenePayout\Support\Ulid;

final readonly class InitiatePaymentUseCase implements InitiatePaymentUseCaseInterface
{
    /**
     * @param Closure(DatabaseQueryExecutorInterface): PaymentExecutionRepositoryInterface $paymentsFactory
     * @param Closure(DatabaseQueryExecutorInterface): ReceivedInvoiceRepositoryInterface $invoicesFactory
     * @param RequestScopedHolder<string> $orgId
     */
    public function __construct(
        private ReceivedInvoiceRepositoryInterface $invoices,
        private PaymentGatewayInterface $gateway,
        private DatabaseTransactionManagerInterface $tx,
        private Closure $paymentsFactory,
        private Closure $invoicesFactory,
        private AuditRecorderFactoryInterface $auditFactory,
        private RequestScopedHolder $orgId,
        private ClockInterface $clock,
    ) {
    }

    public function execute(?string $actorUserId, InitiatePaymentInput $input): InitiatePaymentOutput
    {
        $invoice = $this->invoices->findById($input->receivedInvoiceId);

        if ($invoice === null) {
            throw new ReceivedInvoiceNotFoundException($input->receivedInvoiceId);
        }

        if ($invoice->status !== ReceivedInvoiceStatus::Pending->value) {
            throw new PaymentNotAllowedException($invoice->status);
        }

        $organizationId = $this->orgId->get();
        $paymentId = Ulid::generate();
        $initiatedAt = $this->clock->now()->format('Y-m-d H:i:s');

        // Create the hosted charge session at the gateway (no PAN — ADR 0010).
        $charge = $this->gateway->createCharge(new ChargeRequest(
            organizationId: $organizationId,
            receivedInvoiceId: $input->receivedInvoiceId,
            paymentExecutionId: $paymentId,
            amount: $invoice->amount,
            returnUrl: $input->returnUrl,
        ));

        $payment = new PaymentExecution(
            receivedInvoiceId: $input->receivedInvoiceId,
            amount: $invoice->amount,
            gateway: $input->gateway,
            status: PaymentExecutionStatus::Initiated->value,
            organizationId: $organizationId,
            gatewayReference: $charge->gatewayReference,
            id: $paymentId,
            initiatedAt: $initiatedAt,
        );

        $this->tx->transactional(function (DatabaseQueryExecutorInterface $exec) use ($actorUserId, $organizationId, $payment, $input): void {
            ($this->paymentsFactory)($exec)->save($payment);
            ($this->invoicesFactory)($exec)->updateStatus($input->receivedInvoiceId, ReceivedInvoiceStatus::Processing->value);

            $this->auditFactory->forExecutor($exec)->record(new AuditEvent(
                action: 'payment.initiated',
                entityType: 'payment_execution',
                entityId: $payment->id,
                actorId: $actorUserId,
                organizationId: $organizationId,
                before: null,
                after: PaymentExecutionResponse::toArray($payment),
                id: Ulid::generate(),
            ));
        });

        return new InitiatePaymentOutput(
            paymentExecution: $payment,
            gatewayRedirectUrl: $charge->redirectUrl,
            clientToken: $charge->clientToken,
        );
    }
}
