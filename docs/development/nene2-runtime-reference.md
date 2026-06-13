# NENE2 Runtime Reference — Common Objects, Data Flow & Calling Conventions

**Binding.** This file is the source of truth for *how* NeNe Payout uses the
NENE2 runtime: which framework objects to use (by exact class name), how a
request flows through the layers, how code is wired and called, and where each
kind of code lives. It mirrors `../NENE2/docs/development/` and the real classes
in `../NENE2/src/`. When a NENE2 object exists, **use it — do not reinvent it**.

> Authoritative upstream: `vendor/hideyukimori/nene2/docs/` (once installed) and
> the NENE2 repository `docs/development/`. This file restates the rules so a
> contributor never has to guess a class name. If this file and NENE2 ever
> disagree, NENE2 wins and this file is a bug.

---

## 1. Common objects — use these exact classes (do not reinvent)

All classes are in the `Nene2\` namespace.

| Concern | Use this (exact) | Namespace | Never do |
| --- | --- | --- | --- |
| JSON success response | `JsonResponseFactory` | `Nene2\Http` | hand-rolled `json_encode()` + header |
| Problem Details error | `ProblemDetailsResponseFactory` | `Nene2\Error` | custom error arrays / ad-hoc status |
| Validation error value | `ValidationError` | `Nene2\Validation` | bespoke error shape |
| Validation failure throw | `ValidationException` | `Nene2\Validation` | returning errors from a use case |
| Field validators | `V` | `Nene2\Validation` | scattered inline checks |
| Pagination input | `PaginationQuery` / `PaginationQueryParser` | `Nene2\Http` | custom page/offset parsing |
| Pagination output | `PaginationResponse` | `Nene2\Http` | ad-hoc envelope |
| JSON body parsing | `JsonRequestBodyParser` (`JsonBodyParseException`) | `Nene2\Http` | raw `json_decode` without error handling |
| DB read/write | `DatabaseQueryExecutorInterface` | `Nene2\Database` | raw `PDO` in a repository |
| DB connection build | `DatabaseConnectionFactoryInterface` / `PdoConnectionFactory` | `Nene2\Database` | `new PDO(...)` in app code, singletons |
| DB transaction | `DatabaseTransactionManagerInterface` | `Nene2\Database` | manual `BEGIN`/`COMMIT` strings |
| Bearer auth middleware | `BearerTokenMiddleware` + `TokenVerifierInterface` | `Nene2\Auth` | custom JWT parsing in a handler |
| Local JWT verify (HS256) | `LocalBearerTokenVerifier` (`NENE2_LOCAL_JWT_SECRET`) | `Nene2\Auth` | rolling your own HMAC |
| API-key auth | `ApiKeyAuthenticationMiddleware` | `Nene2\Middleware` | custom header checks |
| Request id | `RequestIdMiddleware` | `Nene2\Middleware` | custom id header logic |
| Request logging | `RequestLoggingMiddleware` | `Nene2\Middleware` | ad-hoc logging in handlers |
| Security headers | `SecurityHeadersMiddleware` | `Nene2\Middleware` | setting headers per-response |
| CORS | `CorsMiddleware` | `Nene2\Middleware` | hard-coded origins |
| Request size limit | `RequestSizeLimitMiddleware` | `Nene2\Middleware` | reading body before limiting |
| Rate limiting | `ThrottleMiddleware` (+ `RateLimitStorageInterface`) | `Nene2\Middleware` | custom counters |
| Pipeline dispatch | `MiddlewareDispatcher` | `Nene2\Middleware` | bespoke pipeline |
| Routing | `Router` | `Nene2\Routing` | attribute-routing magic |
| Error → response mapping | `ErrorHandlerMiddleware` + `DomainExceptionHandlerInterface` | `Nene2\Error` | try/catch in handlers for HTTP shaping |
| Container | `Container` / `ContainerBuilder` | `Nene2\DependencyInjection` | service-locator inside domain code |
| Service registration | `ServiceProviderInterface` | `Nene2\DependencyInjection` | global wiring blob |
| Clock (now) | `ClockInterface` → `UtcClock` | `Nene2\Http` | ambient `date()` / `new DateTimeImmutable()` |
| Request-scoped value (e.g. tenant org_id) | `RequestScopedHolder` | `Nene2\Http` | global/static request state |
| Token hashing | `SecureTokenHelper` | `Nene2\Http` | bespoke hashing |
| Conditional GET / write | `ConditionalGetHelper` / `ConditionalWriteHelper` | `Nene2\Http` | manual ETag handling |
| Typed config | `AppConfig` / `DatabaseConfig` (via `ConfigLoader`) | `Nene2\Config` | `getenv()` in app code |
| HTML (thin) | `HtmlResponseFactory` / `NativePhpViewRenderer` / `HtmlEscaper` | `Nene2\View` | echoing untrusted values |

> There is **no** `PdoConnection::getInstance()`, **no** `DbUpsert`, **no**
> `BearerAuth`, and **no** `ResponseDecorator` in NENE2. Earlier drafts that
> named these were wrong. Use the classes above.

---

## 2. Request data flow (binding)

```text
public_html/index.php  (front controller)
  → PSR-7 ServerRequest
    → Middleware pipeline (MiddlewareDispatcher)
      → Router  (matches method + path)
        → Handler            parse request → build Input DTO → call UseCase → build response
          → UseCase          business invariants; no HTTP/DB knowledge
            → RepositoryInterface          data-access contract (domain terms)
              → PdoXxxRepository           SQL via DatabaseQueryExecutorInterface
```

Rules:

- **Handler is thin**: read PSR-7, validate *format*, construct a readonly Input
  DTO, call the UseCase via its interface, return a response built with
  `JsonResponseFactory`. No business logic, no SQL, no repository calls.
- **UseCase** enforces business invariants and tenant rules. It receives a
  readonly Input DTO and returns a typed Output DTO — never PSR-7 objects, never
  raw arrays. It has **no** HTTP/DB knowledge and **never** touches the container.
- **RepositoryInterface** uses domain verbs (`findById`, `existsByName`,
  `save`), returns domain objects/primitives — never PDO rows.
- **PdoXxxRepository** holds all SQL, depends on `DatabaseQueryExecutorInterface`,
  and casts row values to typed PHP values.

### Path parameters (binding gotcha)

Matched path params live under `Router::PARAMETERS_ATTRIBUTE` as a named array —
**not** as individual PSR-7 attributes.

```php
$params = $request->getAttribute(Router::PARAMETERS_ATTRIBUTE, []);
$id = (string) ($params['id'] ?? '');
// $request->getAttribute('id') returns null — never read it that way.
```

### Auth claims

On success `BearerTokenMiddleware` sets:

| Attribute | Value |
| --- | --- |
| `nene2.auth.credential_type` | `"bearer"` |
| `nene2.auth.claims` | decoded JWT payload (`array<string,mixed>`) |

Auth gives **user identity and role**. The **tenant** (`organization_id`) is a
separate concern: it is resolved from the request by `OrgResolverMiddleware` and
held in a `RequestScopedHolder<string>` that repositories inject and read
(ADR 0018). Never derive `organization_id` for scoping from the request body,
path, or query. Full design: [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md).

---

## 3. Layered validation (binding)

| Layer | Responsibility |
| --- | --- |
| Middleware | request size, content-type, JSON parse, auth, CORS, request id |
| Handler / Mapper | path/query/body mapping, **format** validation, build readonly DTO |
| UseCase | **business invariants**, tenant & state rules |

- Format validation in the Handler (or a focused `XxxInputMapper`) collects
  `ValidationError`s and throws `ValidationException`, which the error boundary
  maps to a `validation-failed` Problem Details (HTTP 422).
- Business invariants (uniqueness, allowed state transitions, tenant ownership)
  are thrown as **named domain exceptions** from the UseCase and mapped to stable
  Problem Details by `ErrorHandlerMiddleware` — never shaped inside the Handler.

---

## 4. Middleware pipeline order (binding)

Inherits NENE2's documented order. `ErrorHandlerMiddleware` wraps everything.

```text
1. ErrorHandlerMiddleware        (wraps the whole pipeline)
2. RequestIdMiddleware
3. SecurityHeadersMiddleware
4. CorsMiddleware
5. RequestSizeLimitMiddleware
6. BearerTokenMiddleware / ApiKeyAuthenticationMiddleware
7. (request/OpenAPI validation, when added)
8. Router / handler dispatch
```

CORS origins are config-driven (allowlist in production). Security header set,
request-id header (`X-Request-Id`), and size limits follow NENE2 defaults.

Payout adds `OrgResolverMiddleware` (tenant resolution → `RequestScopedHolder`)
after authentication and before routing, then a `CapabilityMiddleware` for
authorization. See [`../explanation/multi-tenancy.md`](../explanation/multi-tenancy.md) §3.

---

## 5. Dependency injection & calling conventions (binding)

- PSR-11 is the container boundary (`Nene2\DependencyInjection\Container`).
- Wiring is **explicit** via a `ServiceProviderInterface` per domain concept.
  Bind interfaces to factories; construct dependencies directly.
- **Constructor injection only.** No `new` for testable dependencies. No
  service-locator (`$container->get()`) inside UseCases or domain objects.

```php
final class PaymentServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerBuilder $builder): void
    {
        $builder->bind(
            PaymentExecutionRepositoryInterface::class,
            static fn (ContainerInterface $c) => new PdoPaymentExecutionRepository(
                $c->get(DatabaseQueryExecutorInterface::class),
            ),
        );

        $builder->bind(
            InitiatePaymentUseCaseInterface::class,
            static fn (ContainerInterface $c) => new InitiatePaymentUseCase(
                $c->get(PaymentExecutionRepositoryInterface::class),
                $c->get(PaymentGatewayInterface::class),
                $c->get(ClockInterface::class),
            ),
        );

        $builder->bind(
            InitiatePaymentHandler::class,
            static fn (ContainerInterface $c) => new InitiatePaymentHandler(
                $c->get(InitiatePaymentUseCaseInterface::class),
                $c->get(JsonResponseFactory::class),
            ),
        );
    }
}
```

- Providers are registered explicitly in the runtime container factory; provider
  order must be explicit when it matters.
- Time comes from `ClockInterface` (`UtcClock`), injected — never ambient
  `date()` — so payment/settlement dates are deterministic and testable
  (ADR 0012).

---

## 6. Error responses (binding)

- All public JSON errors are RFC 9457 Problem Details
  (`application/problem+json`) via `ProblemDetailsResponseFactory`.
- Problem `type` is a stable URI, not an exception class name.
- Never leak SQL, stack traces, file paths, secrets, tokens, or PAN in any
  response (payment-compliance §3).
- Validation failures: `422` with a structured `errors[]` (`field`, `message`,
  `code`).

---

## 7. Where code lives (binding)

Group by **domain concept**, not by layer type. No top-level `UseCases/`,
`Handlers/`, `Repositories/` directories.

```
src/
  {Domain}/                      e.g. ReceivedInvoice, Vendor, Payment
    Handler/                     thin HTTP handlers
    UseCase/                     XxxUseCaseInterface + XxxUseCase
    Repository/                  XxxRepositoryInterface + PdoXxxRepository
    Entity/                      readonly domain objects + backed enums
    XxxInput.php / XxxOutput.php readonly DTOs
    XxxInputMapper.php           when mapping is non-trivial
    XxxException.php             named domain exceptions
  ServiceProvider/               one provider per domain concept
```

See [`backend-standards.md`](./backend-standards.md) for the full directory tree
and [`naming-conventions.md`](./naming-conventions.md) for identifier rules.

---

## Related

- Layering & DTO/repository shapes: [`backend-standards.md`](./backend-standards.md)
- NENE2 compliance (binding object list): [`nene2-compliance.md`](./nene2-compliance.md)
- Database & schema: [`database-standards.md`](./database-standards.md)
- Frontend: [`frontend-standards.md`](./frontend-standards.md)
- Naming: [`naming-conventions.md`](./naming-conventions.md)
- Coding standards: [`coding-standards.md`](./coding-standards.md)
