<?php

declare(strict_types=1);

namespace NenePayout\Tests\Payment;

use Nene2\Audit\AuditRecorderFactoryInterface;
use Nene2\Database\DatabaseQueryExecutorInterface;
use Nene2\Http\RequestScopedHolder;
use NenePayout\Payment\Gateway\ChargeRequest;
use NenePayout\Payment\Gateway\ChargeResult;
use NenePayout\Payment\Gateway\PaymentGatewayInterface;
use NenePayout\Payment\InitiatePaymentInput;
use NenePayout\Payment\InitiatePaymentUseCase;
use NenePayout\Payment\PaymentExecutionRepositoryInterface;
use NenePayout\Payment\PaymentNotAllowedException;
use NenePayout\ReceivedInvoice\ReceivedInvoice;
use NenePayout\ReceivedInvoice\ReceivedInvoiceNotFoundException;
use NenePayout\ReceivedInvoice\ReceivedInvoiceRepositoryInterface;
use NenePayout\Tests\Audit\InMemoryAuditRecorderFactory;
use NenePayout\Tests\ReceivedInvoice\InMemoryReceivedInvoiceRepository;
use NenePayout\Tests\Support\FixedClock;
use NenePayout\Tests\Support\ImmediateTransactionManager;
use PHPUnit\Framework\TestCase;

final class InitiatePaymentUseCaseTest extends TestCase
{
    private InMemoryAuditRecorderFactory $auditRepo;

    /** @var RequestScopedHolder<string> */
    private RequestScopedHolder $orgId;

    protected function setUp(): void
    {
        $this->auditRepo = new InMemoryAuditRecorderFactory(new FixedClock());
        /** @var RequestScopedHolder<string> $holder */
        $holder = new RequestScopedHolder();
        $holder->set('01ORG00000000000000000001');
        $this->orgId = $holder;
    }

    private function gateway(): PaymentGatewayInterface
    {
        return new class () implements PaymentGatewayInterface {
            public function createCharge(ChargeRequest $request): ChargeResult
            {
                return new ChargeResult('ref_' . $request->paymentExecutionId, 'https://gw.example/checkout', null);
            }
        };
    }

    private function auditFactory(): AuditRecorderFactoryInterface
    {
        return $this->auditRepo;
    }

    private function invoice(string $status): ReceivedInvoice
    {
        return new ReceivedInvoice(
            vendorId: '01VENDOR000000000000000001',
            amount: 100000,
            dueDate: '2026-07-31',
            status: $status,
            organizationId: '01ORG00000000000000000001',
            id: '01INV00000000000000000001',
        );
    }

    private function useCase(InMemoryReceivedInvoiceRepository $invoices, InMemoryPaymentExecutionRepository $payments): InitiatePaymentUseCase
    {
        return new InitiatePaymentUseCase(
            $invoices,
            $this->gateway(),
            new ImmediateTransactionManager(),
            static fn (DatabaseQueryExecutorInterface $exec): PaymentExecutionRepositoryInterface => $payments,
            static fn (DatabaseQueryExecutorInterface $exec): ReceivedInvoiceRepositoryInterface => $invoices,
            $this->auditFactory(),
            $this->orgId,
            new FixedClock(),
        );
    }

    public function test_initiates_payment_and_transitions_invoice_to_processing(): void
    {
        $invoices = new InMemoryReceivedInvoiceRepository($this->invoice('pending'));
        $payments = new InMemoryPaymentExecutionRepository();

        $output = $this->useCase($invoices, $payments)->execute(
            '01USER0000000000000000001',
            new InitiatePaymentInput('01INV00000000000000000001', 'stripe', 'https://app/return'),
        );

        self::assertSame('initiated', $output->paymentExecution->status);
        self::assertSame('stripe', $output->paymentExecution->gateway);
        self::assertSame(100000, $output->paymentExecution->amount);
        self::assertSame('https://gw.example/checkout', $output->gatewayRedirectUrl);

        self::assertCount(1, $payments->saved);
        self::assertSame('processing', $invoices->findById('01INV00000000000000000001')?->status);

        self::assertCount(1, $this->auditRepo->appended);
        self::assertSame('payment.initiated', $this->auditRepo->appended[0]->action);
    }

    public function test_rejects_payment_when_invoice_not_pending(): void
    {
        $invoices = new InMemoryReceivedInvoiceRepository($this->invoice('processing'));
        $payments = new InMemoryPaymentExecutionRepository();

        $this->expectException(PaymentNotAllowedException::class);
        $this->useCase($invoices, $payments)->execute(null, new InitiatePaymentInput('01INV00000000000000000001', 'stripe'));
    }

    public function test_rejects_payment_for_unknown_invoice(): void
    {
        $invoices = new InMemoryReceivedInvoiceRepository();
        $payments = new InMemoryPaymentExecutionRepository();

        $this->expectException(ReceivedInvoiceNotFoundException::class);
        $this->useCase($invoices, $payments)->execute(null, new InitiatePaymentInput('missing', 'stripe'));
    }
}
