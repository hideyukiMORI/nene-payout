# Backend Standards — NeNe Payout

Inherits NENE2. This file records Payout-specific rules.

## Directory layout

```
src/
  ReceivedInvoice/
    Handler/
      ListHandler.php
      ShowHandler.php
      CreateHandler.php
    UseCase/
      ListReceivedInvoicesUseCase.php
      CreateReceivedInvoiceUseCase.php
    Repository/
      ReceivedInvoiceRepositoryInterface.php
      PdoReceivedInvoiceRepository.php
    Entity/
      ReceivedInvoice.php
      ReceivedInvoiceStatus.php   (backed enum)
  Vendor/
    ...
  Payment/
    Gateway/
      PaymentGatewayInterface.php
      ChargeRequest.php
      ChargeResult.php
    Stripe/
      StripeGatewayAdapter.php
    Handler/
      InitiatePaymentHandler.php
      WebhookHandler.php
    UseCase/
      InitiatePaymentUseCase.php
    Repository/
      PaymentExecutionRepositoryInterface.php
      PdoPaymentExecutionRepository.php
  ServiceProvider/
    ReceivedInvoiceServiceProvider.php
    VendorServiceProvider.php
    PaymentServiceProvider.php
```

## Layering rules

- Handler → UseCase → RepositoryInterface → PdoRepository
- Handler is thin: parse request → call UseCase → build response
- UseCase has no HTTP/DB knowledge
- Repository implementation lives in `PdoXxxRepository`

## Tenant isolation (binding)

- Every query on a tenant table **must** include `organization_id` in WHERE
- Extract `org_id` from JWT in Handler; pass to UseCase as parameter
- Never trust `organization_id` from request body — always use JWT claim

## Payment gateway adapter (binding)

- `PaymentGatewayInterface` is the only entry point for charging
- `StripeGatewayAdapter` and other adapters implement this interface
- Raw card numbers must never pass through Payout — use gateway iframe / tokenization
- Webhook signature must be verified before processing

## PDO injection pattern

```php
public function __construct(private readonly ?PDO $db = null) {}

private function db(): PDO
{
    return $this->db ?? PdoConnection::getInstance();
}
```

## Error responses

All error responses use RFC 9457 Problem Details (`application/problem+json`) via NENE2 `ProblemDetailsResponseFactory`.

## CONTRIBUTING.md cross-reference

See [`docs/CONTRIBUTING.md`](../CONTRIBUTING.md) for PR process, code review checklist, and commit conventions.
