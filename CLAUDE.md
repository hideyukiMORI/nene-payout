# CLAUDE.md — NeNe Payout

Claude Code / AI agent guide for this repository. Cursor summaries live in `.cursor/rules/`.

## Source of Truth

| Purpose | Document |
| --- | --- |
| Scope contract (binding) | `docs/explanation/scope-contract.md` |
| **Payment/legal/tax compliance (binding)** | `docs/explanation/payment-compliance.md` |
| Compliance self-review (binding) | `docs/review/compliance.md` |
| Canonical terms (binding) | `docs/terms.md` |
| NENE2 inheritance | `docs/inheritance-from-nene2.md` |
| Agent entry | `AGENTS.md` |
| Workflow | `docs/workflow.md` |
| Commits | `docs/development/commit-conventions.md` |
| Coding (index) | `docs/development/coding-standards.md` |
| Backend standards (binding) | `docs/development/backend-standards.md` |
| NENE2 compliance (binding) | `docs/development/nene2-compliance.md` |
| NENE2 runtime reference (binding) | `docs/development/nene2-runtime-reference.md` |
| Database & schema (binding) | `docs/development/database-standards.md` |
| Frontend standards (binding) | `docs/development/frontend-standards.md` |
| Current tasks | `docs/todo/current.md` |
| Roadmap | `docs/roadmap.md` |

## Quick Rules

- **Issue-driven**: no Issue, no code/doc change (except explicit user scope limits).
- **Branch**: `type/issue-number-summary` from `main`; never commit directly to `main`.
- **Commits**: Conventional Commits; type/scope English, description/body Japanese, include `(#issue)`.
- **PR**: purpose, changes, verification, checklist name, `Closes #n`.
- **Secrets**: never commit `.env`, tokens, or credentials.
- **Framework**: NENE2 via Composer — read `vendor/hideyukimori/nene2/docs/` for runtime patterns.
- **Coding (binding)**: follow NENE2 conventions exactly (ADR 0016). Use real NENE2 classes per `docs/development/nene2-runtime-reference.md` — there is NO `PdoConnection::getInstance()`, `DbUpsert`, `BearerAuth`, or `ResponseDecorator`; use `DatabaseQueryExecutorInterface`, `BearerTokenMiddleware`, `SecurityHeadersMiddleware`. Layer Handler→UseCase→RepositoryInterface→PdoRepository; constructor injection; no service locator in domain code.
- **Terms**: every identifier must match `docs/terms.md` exactly. Check before writing any name.
- **Scope**: Payout pays vendor invoices by card. Does NOT issue invoices, reconcile deposits, or archive documents.
- **Compliance (binding)**: `docs/explanation/payment-compliance.md` is non-negotiable. Payout is software only — all regulated money movement is delegated to the licensed gateway (ADR 0009); no PAN (SAQ-A, ADR 0010); financial records immutable & retained (ADR 0013); fee/refund/chargeback accounting needs a 税理士/会計士-signed ADR (ADR 0015). Run `docs/review/compliance.md` for any change with possible compliance impact.

## Product Direction

Self-hosted vendor invoice payment platform. Businesses register received invoices,
pay by credit card, and the payment gateway transfers funds to the vendor's bank account.
An embeddable widget allows integration into any existing system. All data stays on the
operator's own server.

## Local stack

| Service | URL | Env var |
| --- | --- | --- |
| API | http://localhost:8900 | `NENE_PAYOUT_PORT` |
| phpMyAdmin | http://localhost:8901 | `NENE_PAYOUT_PHPMYADMIN_PORT` |
| MySQL (host) | localhost:3398 | `NENE_PAYOUT_MYSQL_PORT` |
| Frontend dev | http://localhost:5189 | `NENE_PAYOUT_FRONTEND_PORT` |

Health check: `curl -fsS http://localhost:8900/health`

## Verification

```bash
composer check
npm run check --prefix frontend
```
