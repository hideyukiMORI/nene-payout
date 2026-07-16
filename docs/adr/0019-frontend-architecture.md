# ADR 0019 — Frontend Architecture: Strict FSD + Mandated Stack (binding)

Date: 2026-06-13
Status: Accepted

## Context

NeNe Payout needs a frontend (admin UI + embeddable payment widget) that is
predictable, secure, and consistent with the NeNe ecosystem. The sibling product
`nene-records` runs a strict, proven React architecture (Feature-Sliced Design
with zero-tolerance placement, unidirectional data flow, TanStack Query, strict
TypeScript, Tailwind-token-only theming, Storybook contracts, MSW testing). The
user requires the strictest practical industry conventions, with placement and
data flow firmly constrained.

## Decision

- Adopt the strict frontend architecture defined in
  [`docs/development/frontend-standards.md`](../development/frontend-standards.md)
  and enforced by [`docs/review/frontend.md`](../review/frontend.md) and
  `.cursor/rules/30-frontend-react.mdc`. That document is **binding**; violations
  of placement, dependency direction, data flow, theming, security, or testing
  **block merge**.
- **Architecture:** Feature-Sliced Design layers `app → pages → features →
  entities → shared`; no upward or cross-feature imports; slices expose `index.ts`
  only; ESLint encodes the boundaries.
- **Mandated stack:** React + TypeScript + Vite + npm; React Router; TanStack
  Query v5; React Hook Form + Zod; Tailwind v4 with `@theme` CSS-custom-property
  tokens; Storybook; Vitest + Testing Library + MSW; Playwright (later); knip.
  Alternatives (Redux/Zustand, CSS Modules/CSS-in-JS, other UI stacks) require an
  ADR.
- **Payout specifics (binding):** integer-money formatting only at the view edge;
  UTC→JST display (ADR 0012); **no card PAN in the frontend** (ADR 0010, gateway
  hosted capture); tenant `organization_id` is server-resolved, never sent by the
  UI for scoping (ADR 0018); ja/en i18n via `shared/i18n` (i18n.md); fail-closed
  auth.

## Consequences

- One predictable structure; the import graph encodes the architecture and is
  machine-enforced (ESLint boundaries, knip, `--max-warnings 0`).
- A larger toolchain (Storybook, MSW, Playwright) and stricter review, accepted as
  the cost of long-term maintainability and security.
- Frontend implementation begins in Phase 2 (admin UI + widget); these rules apply
  to any frontend PR from now.

## Related

- Standards: `docs/development/frontend-standards.md`
- Self-review: `docs/review/frontend.md`
- i18n: `docs/explanation/i18n.md`
- Reference implementation: `../nene-records` `frontend/`
- Hooks segment location: this ADR defines FSD **layers** and delegates all
  **placement specifics** to `frontend-standards.md` (binding). Where a fleet
  regulation supersedes this repo's prior binding, that document is the authority
  and is revised accordingly — orchestration hooks now live in
  `features/{feature}/model/` (fleet reg 05:916; see #194 / PR #195). This ADR
  itself is **not** superseded: it prescribes no segment placement and stands.
