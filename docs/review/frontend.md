# Frontend Self-Review — NeNe Payout

**Binding.** Use for any admin UI or widget React/TypeScript change. Policy source:
[`../development/frontend-standards.md`](../development/frontend-standards.md)
(ADR 0019). Reviewers reject merge on placement, dependency, data-flow, theming,
or security violations — no deferrals, no "fix later".

## Architecture & dependencies

- [ ] Imports follow `app → pages → features → entities → shared` only (no upward, no cross-feature).
- [ ] Entities/features imported only via their `index.ts` public surface.
- [ ] ESLint boundary rules pass; no sibling `entities/*` imports.

## Placement (zero tolerance)

- [ ] Entity folder matches canonical tree (`ids`, `model`, `mapper`, `query-keys`, `queries`, `mutations`, `index.ts`; `enum`/`api-types` when needed); folder is kebab-case = OpenAPI tag.
- [ ] No models/enums/DTOs/TanStack hooks/MSW handlers outside mandated paths.
- [ ] No `shared/api/generated/` or `api-types` import from `features/`, `pages/`, or `*.tsx` (except colocated `*Props`).
- [ ] No root `src/types/` or ad-hoc dumps in `utils.ts`/`helpers.ts`; generated types only in `shared/api/generated/` (not hand-edited).

## Data flow

- [ ] Read: API → client → mapper → entity query → feature hook → UI (models only in UI).
- [ ] Write: UI → mutation hook → client → API; explicit query invalidation on success.
- [ ] No `useEffect`+`fetch` for server data; no API responses in `useState`.
- [ ] Screens implement loading / empty / error / success explicitly.
- [ ] Optimistic updates roll back on Problem Details failure and have a test.

## Money, dates & compliance

- [ ] Money handled as integer minimum units; formatting only at the view edge (no float math); `amount`/`charge_amount`/`processing_fee` kept distinct.
- [ ] UTC datetimes displayed in JST (ADR 0012).
- [ ] No card PAN/CVV anywhere in frontend; capture is gateway hosted iframe/redirect only (ADR 0010).
- [ ] UI never sends `organization_id` for scoping — server resolves tenant (ADR 0018).

## Design system & theming

- [ ] All visual tokens live in `shared/ui/theme/themes/*.css`; `active.css` is the only full-theme swap point.
- [ ] No hex/rgb/hsl/px literals or Tailwind arbitrary values (`[…]`) in components/features/pages.
- [ ] Components use semantic Tailwind utilities only; features import UI from the `shared/ui` barrel (no deep `primitives/` paths, no styled raw HTML).

## Storybook

- [ ] Every exported `shared/ui` primitive & composed component has a colocated `*.stories.tsx`.
- [ ] Story header documents **In / Out / Does not**; covers default, disabled (if any), all variants; no API/Query/router in primitive stories.
- [ ] `npm run build-storybook --prefix frontend` passes.

## Design patterns & TypeScript

- [ ] Feature split: `hooks/` (logic) + `ui/` (presentational); no fetch/query in `shared/ui`.
- [ ] Query keys only from `query-keys.ts`; forms use React Hook Form + Zod; destructive actions use a confirm dialog.
- [ ] Named exports only; no class components; no default exports.
- [ ] No `any`, unjustified `@ts-ignore`, or `!` without invariant comment; branded IDs in `ids.ts`; env via `shared/config/env.ts`.

## API & security

- [ ] HTTP only in `shared/api/client.ts`; Problem Details parsed in `shared/api/errors.ts`.
- [ ] No secrets/tokens in source; auth storage matches API policy.
- [ ] 401/403 fail closed; no silent unauthenticated writes; RBAC UI gating is UX only.
- [ ] No `dangerouslySetInnerHTML` without sanitization policy + Issue.

## i18n

- [ ] All user-facing strings via `t(key)` (no hardcoded ja/en); new keys in `messages/en.ts` AND `ja.ts`; key-parity test passes.
- [ ] Errors localized by mapping problem `type`/`code` to catalog messages.

## Testing & CI

- [ ] Entity: mapper tests (+ query-key tests if non-trivial); hook tests with MSW for primary query/mutation.
- [ ] Feature: ≥1 feature-hook test (MSW) + component happy + primary error path.
- [ ] MSW matches OpenAPI; factories build models; queries by role/label + `userEvent`; no shallow child mocks.
- [ ] `npm run check --prefix frontend` passes; `node_modules`/generated assets not committed; lockfile updated if deps changed.

## PR

- [ ] PR body includes `Self-review: frontend` and the verification commands run.
