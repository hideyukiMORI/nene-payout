<?php

declare(strict_types=1);

namespace NenePayout\Tests\Payment;

use NenePayout\Payment\GetPaymentExecutionUseCase;
use NenePayout\Payment\ListPaymentExecutionsUseCase;
use NenePayout\Payment\PaymentExecution;
use NenePayout\Payment\PaymentExecutionFilter;
use NenePayout\Payment\PaymentExecutionNotFoundException;
use PHPUnit\Framework\TestCase;

final class PaymentQueryUseCaseTest extends TestCase
{
    private function payment(string $id, string $status = 'initiated', string $invoiceId = '01I'): PaymentExecution
    {
        return new PaymentExecution(
            receivedInvoiceId: $invoiceId,
            amount: 100000,
            gateway: 'stripe',
            status: $status,
            organizationId: '01ORG00000000000000000001',
            id: $id,
            initiatedAt: '2026-06-13 00:00:00',
        );
    }

    public function test_list_empty_returns_zero(): void
    {
        $out = (new ListPaymentExecutionsUseCase(new InMemoryPaymentExecutionRepository()))
            ->execute(new PaymentExecutionFilter(), 20, 0);

        self::assertSame(0, $out->total);
    }

    public function test_list_status_filter(): void
    {
        $repo = new InMemoryPaymentExecutionRepository(
            $this->payment('01A', 'initiated'),
            $this->payment('01B', 'succeeded'),
        );

        $out = (new ListPaymentExecutionsUseCase($repo))->execute(new PaymentExecutionFilter(status: 'succeeded'), 20, 0);

        self::assertSame(1, $out->total);
        self::assertSame('succeeded', $out->items[0]->status);
    }

    public function test_list_invoice_filter_and_pagination(): void
    {
        $repo = new InMemoryPaymentExecutionRepository(
            $this->payment('01A', 'initiated', '01I1'),
            $this->payment('01B', 'failed', '01I1'),
            $this->payment('01C', 'initiated', '01I2'),
        );
        $useCase = new ListPaymentExecutionsUseCase($repo);

        $filtered = $useCase->execute(new PaymentExecutionFilter(receivedInvoiceId: '01I1'), 20, 0);
        self::assertSame(2, $filtered->total);

        self::assertCount(1, $useCase->execute(new PaymentExecutionFilter(receivedInvoiceId: '01I1'), 1, 0)->items);
        self::assertCount(1, $useCase->execute(new PaymentExecutionFilter(receivedInvoiceId: '01I1'), 1, 1)->items);
        self::assertCount(0, $useCase->execute(new PaymentExecutionFilter(receivedInvoiceId: '01I1'), 1, 5)->items);
    }

    public function test_get_returns_payment(): void
    {
        $repo = new InMemoryPaymentExecutionRepository($this->payment('01A'));

        self::assertSame('01A', (new GetPaymentExecutionUseCase($repo))->execute('01A')->id);
    }

    public function test_get_throws_when_missing(): void
    {
        $useCase = new GetPaymentExecutionUseCase(new InMemoryPaymentExecutionRepository());

        $this->expectException(PaymentExecutionNotFoundException::class);
        $useCase->execute('missing');
    }
}
