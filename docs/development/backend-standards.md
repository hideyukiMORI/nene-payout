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
- `organization_id` is resolved from the **request** by `OrgResolverMiddleware`
  and held in a `RequestScopedHolder<string>` (ADR 0018); repositories inject the
  holder and read it — they do not receive `org_id` as a method parameter
- Never trust `organization_id` from the request body, path, or query for scoping
- User identity / role come from auth (`BearerTokenMiddleware`); tenant context is
  separate. Full design: [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md)

## Payment gateway adapter (binding)

- `PaymentGatewayInterface` is the only entry point for charging
- `StripeGatewayAdapter` and other adapters implement this interface
- Raw card numbers must never pass through Payout — use gateway iframe / tokenization
- Webhook signature must be verified before processing

## Repository persistence pattern (binding)

Repositories depend on NENE2's `DatabaseQueryExecutorInterface` via constructor
injection — **not** raw `PDO`, **not** a singleton/`getInstance()`. Connections
are built by `PdoConnectionFactory` from typed `DatabaseConfig`; multi-query
atomic work uses `DatabaseTransactionManagerInterface`.

```php
final class PdoReceivedInvoiceRepository implements ReceivedInvoiceRepositoryInterface
{
    public function __construct(
        private readonly DatabaseQueryExecutorInterface $query,
    ) {}

    public function findById(string $id, string $organizationId): ?ReceivedInvoice
    {
        $row = $this->query->fetchOne(
            'SELECT id, amount, status FROM received_invoices WHERE id = ? AND organization_id = ?',
            [$id, $organizationId],
        );
        return $row !== null ? /* typed cast */ : null;
    }
}
```

See [`database-standards.md`](./database-standards.md) and
[`nene2-runtime-reference.md`](./nene2-runtime-reference.md) for the full rules.

## Error responses

All error responses use RFC 9457 Problem Details (`application/problem+json`) via
NENE2 `ProblemDetailsResponseFactory`. Domain exceptions are mapped to stable
Problem types at the error boundary (`ErrorHandlerMiddleware` +
`DomainExceptionHandlerInterface`), never shaped inside the Handler.

## CONTRIBUTING.md cross-reference

See [`docs/CONTRIBUTING.md`](../CONTRIBUTING.md) for PR process, code review checklist, and commit conventions.
