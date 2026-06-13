<?php

declare(strict_types=1);

namespace NenePayout\Tests\Payment;

use Nene2\Http\JsonResponseFactory;
use Nene2\Routing\Router;
use Nene2\Validation\ValidationException;
use NenePayout\Payment\InitiatePaymentHandler;
use NenePayout\Payment\InitiatePaymentInput;
use NenePayout\Payment\InitiatePaymentOutput;
use NenePayout\Payment\InitiatePaymentUseCaseInterface;
use NenePayout\Payment\PaymentExecution;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/** Fake that records the input it received. */
final class FakeInitiatePaymentUseCase implements InitiatePaymentUseCaseInterface
{
    public ?InitiatePaymentInput $captured = null;

    public function execute(?string $actorUserId, InitiatePaymentInput $input): InitiatePaymentOutput
    {
        $this->captured = $input;

        $payment = new PaymentExecution(
            receivedInvoiceId: $input->receivedInvoiceId,
            amount: 100000,
            gateway: $input->gateway,
            status: 'initiated',
            organizationId: '01ORG',
            id: '01PAY',
            initiatedAt: '2026-06-13 00:00:00',
        );

        return new InitiatePaymentOutput($payment, 'https://gw/checkout', null);
    }
}

final class InitiatePaymentHandlerTest extends TestCase
{
    private Psr17Factory $psr17;

    protected function setUp(): void
    {
        $this->psr17 = new Psr17Factory();
    }

    /**
     * @param array<string, mixed> $body
     */
    private function request(array $body): ServerRequestInterface
    {
        return $this->psr17->createServerRequest('POST', 'https://example.com/api/v1/received-invoices/01I/payments')
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['received_invoice_id' => '01I'])
            ->withBody($this->psr17->createStream((string) json_encode($body)));
    }

    private function handler(FakeInitiatePaymentUseCase $useCase): InitiatePaymentHandler
    {
        return new InitiatePaymentHandler($useCase, new JsonResponseFactory($this->psr17, $this->psr17));
    }

    public function test_valid_stripe_returns_201_with_payment_body(): void
    {
        $useCase = new FakeInitiatePaymentUseCase();
        $response = $this->handler($useCase)->handle($this->request(['gateway' => 'stripe', 'return_url' => 'https://app/return']));

        self::assertSame(201, $response->getStatusCode());

        $captured = $useCase->captured;
        if ($captured === null) {
            self::fail('Use case was not called.');
        }
        self::assertSame('stripe', $captured->gateway);
        self::assertSame('https://app/return', $captured->returnUrl);

        /** @var array<string, mixed> $body */
        $body = json_decode((string) $response->getBody(), true);
        self::assertSame('https://gw/checkout', $body['gateway_redirect_url']);
        self::assertIsArray($body['payment_execution']);
    }

    public function test_gmo_pg_is_accepted(): void
    {
        $useCase = new FakeInitiatePaymentUseCase();
        $response = $this->handler($useCase)->handle($this->request(['gateway' => 'gmo_pg']));

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('gmo_pg', $useCase->captured?->gateway);
    }

    public function test_invalid_gateway_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler(new FakeInitiatePaymentUseCase())->handle($this->request(['gateway' => 'paypal']));
    }

    public function test_missing_gateway_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->handler(new FakeInitiatePaymentUseCase())->handle($this->request(['return_url' => 'https://app/return']));
    }
}
