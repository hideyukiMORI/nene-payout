# ADR 0016 — NENE2 Coding Conventions Are Binding

Date: 2026-06-13
Status: Accepted

## Context

ADR 0001 established that NeNe Payout inherits NENE2 governance. To make code
credible and consistent across the NeNe ecosystem, the *coding* conventions of
NENE2 — naming, file/placement, common-object usage, data flow, DI, schema, and
frontend structure — must be followed exactly, not approximately. Early Payout
drafts referenced framework objects that do not exist in NENE2
(`PdoConnection::getInstance()`, `DbUpsert`, `BearerAuth`, `ResponseDecorator`),
which would produce non-compilable, non-conformant code.

## Decision

- NENE2 coding conventions are **binding**. Code that deviates does not merge.
- The source of truth for *how* to use the runtime is
  `docs/development/nene2-runtime-reference.md` (exact class names and data flow),
  backed by `nene2-compliance.md`, `backend-standards.md`,
  `database-standards.md`, `frontend-standards.md`, `naming-conventions.md`, and
  `coding-standards.md`.
- Use real NENE2 classes (`Nene2\` namespace): `JsonResponseFactory`,
  `ProblemDetailsResponseFactory`, `ValidationException`/`ValidationError`/`V`,
  `PaginationQuery`, `DatabaseQueryExecutorInterface`,
  `DatabaseConnectionFactoryInterface`/`PdoConnectionFactory`,
  `DatabaseTransactionManagerInterface`, `BearerTokenMiddleware` +
  `TokenVerifierInterface`/`LocalBearerTokenVerifier`, `RequestIdMiddleware`,
  `SecurityHeadersMiddleware`, `CorsMiddleware`, `RequestSizeLimitMiddleware`,
  `ClockInterface`/`UtcClock`, `Container`/`ContainerBuilder`/`ServiceProviderInterface`.
- Layering is Handler → UseCase → RepositoryInterface → PdoRepository, group by
  domain concept, constructor injection only, no service locator in domain code.
- When NENE2 upstream and a Payout doc disagree, **NENE2 wins**; the Payout doc
  is treated as a bug and corrected.
- A deviation from NENE2 conventions requires a local ADR explaining why.

## Consequences

- New code is predictable and matches the framework it runs on.
- The incorrect object references in earlier drafts are corrected in this change.
- Contributors and AI agents have one place (`nene2-runtime-reference.md`) to find
  the correct class for any concern.

## Related

- Inherit governance: `docs/adr/0001-inherit-nene2-governance.md`
- Runtime reference: `docs/development/nene2-runtime-reference.md`
- NENE2 upstream: `vendor/hideyukimori/nene2/docs/` (once installed)
