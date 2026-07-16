# Frontend Standards — NeNe Payout

The NeNe Payout admin UI and the embeddable payment widget are **React +
TypeScript** clients of the JSON API. They are **not** the source of truth for
schema, validation, money math, tax rules, or persistence — the API is.

**Framework baseline:** [NENE2 frontend integration](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/frontend-integration.md)
(directory layout, npm, lockfile, build output, dev proxy). Reference
implementation: `../nene-records` `frontend/`.

**Enforcement level (binding, ADR 0019):** violations of placement, dependency
direction, data flow, theming, security, or testing rules in this document
**block merge to `main`**. No temporary exceptions without an ADR.

---

## Principles

| Principle | Meaning |
| --- | --- |
| **API first** | OpenAPI is the contract; UI reflects API types and Problem Details errors, never replaces validation, money, or tax logic. |
| **Unidirectional flow** | Data flows **down** (API → entity → feature → UI); events flow **up** (UI → feature hook → mutation → API). No sideways shortcuts. |
| **Strict TypeScript** | `strict` + extra guards; no untyped escape hatches. |
| **Fixed placement** | Models, enums, hooks, tests live in mandated paths — **placement violations block merge**. |
| **Explicit dependencies** | Import graph encodes architecture; ESLint enforces it. |
| **Loose coupling** | Layers talk through public surfaces (`index.ts`, props, hooks), not internals. |
| **Secure by default** | Fail closed on auth; never store/handle card PAN (ADR 0010); minimal trust of client input. |
| **Test by behavior** | Tests assert user-observable outcomes; MSW mirrors OpenAPI at boundaries. |
| **Theme by substitution** | All visual values live in theme token files; swapping the active theme restyles the whole app without touching components. |
| **No magic styling** | Margin, padding, color, typography, background never appear as raw literals outside the theme layer. |

---

## Stack (mandated — alternatives require an ADR)

| Layer | Choice | Notes |
| --- | --- | --- |
| UI | **React** (latest stable major) | Function components + hooks only — no class components |
| Language | **TypeScript** (latest stable major) | All source `.ts` / `.tsx` |
| Bundler | **Vite** | Dev server (host port 5190 — `local-ports.md`) + build to `public_html/assets/` |
| Package manager | **npm** | Commit `frontend/package-lock.json`; CI uses `npm ci` |
| Node.js | **Active LTS** | `engines` + `packageManager` in `frontend/package.json` |
| Routing | **React Router** | URL is shareable state |
| Server state | **TanStack Query v5** | Queries, mutations, cache, invalidation |
| Forms | **React Hook Form** + **Zod** | Client UX validation only — API authoritative |
| i18n | homegrown `shared/i18n` (ja/en) | See [`../explanation/i18n.md`](../explanation/i18n.md) |
| Styling | **Tailwind CSS v4** + `@theme` + CSS custom properties | Semantic utilities → theme tokens |
| Design tokens | CSS custom properties in `shared/ui/theme/` | Single source of truth for visual values |
| Catalog | **Storybook** (React + Vite) | Required for `shared/ui` primitives & components |
| Lint | **ESLint** flat config: `typescript-eslint` strict-type-checked, `react-hooks`, `jsx-a11y`, boundaries (`eslint-plugin-boundaries` / `import/no-restricted-paths`), `eslint-plugin-tailwindcss` | `--max-warnings 0` |
| Format | **Prettier** | Single formatter |
| Unit / integration | **Vitest** + **Testing Library** + **MSW** | jsdom |
| E2E | **Playwright** | Critical flows after Phase 2 stabilizes |
| Dead code | **knip** | Fail CI on unused exports in `entities/` & `features/` |

No Redux/Zustand/Jotai; no CSS Modules/CSS-in-JS mix; no alternate UI stack — each needs an ADR.

---

## Architecture (Feature-Sliced Design — strict)

Layers: **`app → pages → features → entities → shared`**. Stricter than generic
FSD: entity modules and API boundaries are NeNe-specific.

### Layer responsibilities

| Layer | Owns | Must not own |
| --- | --- | --- |
| **`shared/`** | Transport, design tokens, i18n, pure utils, env config | Routes, features, resource models, business workflows |
| **`entities/`** | One API resource: DTO mapping, query keys, TanStack hooks | JSX, cross-resource orchestration, feature copy |
| **`features/`** | User workflows composing entities + UI | Raw HTTP, DTO types, direct query-key strings |
| **`pages/`** | Route wiring, lazy loading, layout slots | Business rules, API calls |
| **`app/`** | Providers, router, error boundary, auth gate, i18n/theme bootstrap | Feature-specific screens |

### Dependency rule (hard)

```
app → pages → features → entities → shared
```

No arrow points **upward** (`entities → features`, `shared → entities` are forbidden).
**No cross-feature imports** (`features/foo` importing `features/bar`). Cross-feature
sharing is extracted to `entities/` (resource-level) or `shared/` (generic, with ADR).

### Import matrix (mandatory)

| From ↓ / To → | `shared/ui` | `shared/api` | `shared/lib` | `shared/i18n` | `entities/*` | `features/*` | `pages/*` | `app/*` |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `shared/ui` | ✓ internal | ✗ | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| `shared/api` | ✗ | ✓ internal | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| `entities/{r}` | ✗ | ✓ client only | ✓ | ✓ | ✗ sibling | ✗ | ✗ | ✗ |
| `features/{f}` | ✓ | ✗ | ✓ | ✓ | ✓ via `index.ts` | ✗ cross-feature | ✗ | ✗ |
| `pages/` | ✓ | ✗ | ✓ | ✓ | ✗ direct | ✓ via `index.ts` | ✗ | ✗ |
| `app/` | ✓ | ✓ providers | ✓ | ✓ | ✗ | ✗ | ✓ | ✓ internal |

### Public surfaces

Every `entities/{resource}/` and `features/{feature}/` exposes **`index.ts` only**
to upper layers. Internals (`mapper.ts`, `api-types.ts`, `ui/*.tsx`) are private.
`index.ts` does **not** re-export `api-types`, `mapper`, or generated DTOs.

---

## Repository layout

```text
frontend/
  package.json  package-lock.json
  tsconfig.json  tsconfig.app.json  tsconfig.node.json
  vite.config.ts  eslint.config.js  vitest.config.ts
  .storybook/
  src/
    app/
      providers.tsx          # QueryClientProvider, Router, I18nProvider, theme
      router.tsx
      root-error-boundary.tsx
      auth-gate.tsx          # fail-closed session check (401→login, 403→forbidden)
    pages/
      received-invoices/ReceivedInvoicesPage.tsx
      ...
    features/
      register-received-invoice/
      initiate-payment/
      manage-vendors/
      manage-gateway-settings/
      view-audit-logs/
      ...
    entities/
      received-invoice/  vendor/  payment-execution/
      organization/  gateway-setting/  user/  audit-log/
    widget/                  # embeddable payment widget entry (separate Vite build)
    shared/
      ui/
        theme/               # design tokens — ONLY place for raw visual values
          index.css  active.css  themes/default.css  tokens.ts
        primitives/          # Button, Input, Text, Stack, Money, …
        components/          # Dialog, ConfirmDialog, DataTable shell, EmptyState, …
        index.ts             # public barrel
      api/                   # client.ts, errors.ts, generated/
      i18n/                  # locales, messages/{en,ja}, provider (i18n.md)
      lib/                   # pure utils (money/date formatters, …)
      config/                # env.ts (Zod-validated)
  tests/
    setup/  msw/  factories/  render/
```

Built assets go to `public_html/assets/`; source and `node_modules/` stay out of the doc root.

---

## Type & module placement (zero tolerance)

Each API resource → one `entities/{resource}/` folder (**kebab-case**, matches the
OpenAPI tag). Canonical tree:

```text
entities/received-invoice/
  index.ts          # ONLY public surface
  ids.ts            # branded IDs (ReceivedInvoiceId)
  enum.ts           # resource-scoped enums (status) — optional
  api-types.ts      # DTOs pre-codegen; aliases post-codegen
  model.ts          # UI read model
  mapper.ts         # DTO ↔ model (pure)
  query-keys.ts     # TanStack key factory
  queries.ts        # useQuery hooks
  mutations.ts      # useMutation hooks
  mapper.test.ts
```

### Placement matrix

| Artifact | Required path |
| --- | --- |
| OpenAPI-generated types | `shared/api/generated/` |
| Hand-written DTOs | `entities/{resource}/api-types.ts` |
| Branded IDs | `entities/{resource}/ids.ts` |
| Enums | `entities/{resource}/enum.ts` or `shared/lib/enums/` (ADR) |
| UI models | `entities/{resource}/model.ts` |
| Mappers | `entities/{resource}/mapper.ts` |
| Query keys | `entities/{resource}/query-keys.ts` |
| `useQuery` / `useMutation` | `entities/{resource}/queries.ts` / `mutations.ts` |
| HTTP transport | `shared/api/client.ts` |
| Problem Details parse | `shared/api/errors.ts` |
| Component props | same `.tsx` as component (`{Component}Props`) |
| Feature orchestration hooks | `features/{feature}/model/` |
| MSW handlers | `tests/msw/{resource}.ts` or `entities/{resource}/__msw__/` |
| Test factories (build **models**) | `tests/factories/{resource}.ts` |
| Design token CSS | `shared/ui/theme/themes/*.css` only |
| UI primitives / composed | `shared/ui/primitives/` / `shared/ui/components/` |
| Stories | colocated `shared/ui/**/*.stories.tsx` |

> **Hooks segment location — fleet reg 05:916（移行中・#194）**: orchestration hooks は
> `features/{feature}/model/` に置く。fleet reg が本リポの旧 binding（`hooks/`）を supersede する
> （規約プログラムの成立条件 — binding は fleet reg の下位互換に改訂していく）。
> 既存の `features/*/hooks/`（7 features・命名は `use-kebab-case.ts` で既に正）は**物理移設が未了**で、
> **A1 codemod で移設する（手作業移設禁止・pilot=vault の後に payout へ）**。codemod 到着まで
> `hooks/` はツリーに残る（設計どおりの過渡状態）。①「form 系 model を use-*-form フック化せよ」は
> 05:916 の要求ではない（hook を export しない module は kebab-case のまま正 — fleet #66 差し戻し）。

### Forbidden placements (automatic reject)

- DTOs/API shapes in `features/`, `pages/`, `shared/ui/`, or `.tsx` (except `*Props`)
- Models, enums, mappers outside `entities/{resource}/`
- TanStack logic outside `query-keys.ts` / `queries.ts` / `mutations.ts`
- `fetch` outside `shared/api/client.ts`
- `shared/api/generated/` imported from any `.tsx` or from `features/`
- Deep entity imports from features (bypassing `index.ts`)
- Root `src/types/`, `src/utils/` type dumps

---

## Data flow

### Read path (server → UI)

```text
API JSON → shared/api/client.ts → entities/{r}/api-types.ts → entities/{r}/mapper.ts
  → entities/{r}/queries.ts (TanStack cache) → features/{f}/model → features/{f}/ui (render models)
```

- **Mappers run inside entity hooks**, not components.
- Components receive **`model` types** + callbacks — never `Response`, never DTOs.
- Lists use **stable query keys** from `query-keys.ts` only.

### Write path (UI → server)

```text
UI event → features/{f}/model (or entity mutation hook) → entities/{r}/mutations.ts
  → shared/api/client.ts → API
  → onSuccess: invalidate query-keys (explicit) ; onError: Problem Details → AppError → UI feedback
```

- **Mutations live in `mutations.ts`**; features call exported hooks (no inline `useMutation`).
- Optimistic updates require rollback on failure **and a test** proving rollback.

### URL & shareable state

| State | Location |
| --- | --- |
| Resource id in detail view | route param (`/received-invoices/:id`) |
| Filters, sort, page | `searchParams` (serializable) |
| Modal/tab/hover | local `useState` in feature |
| Server data | TanStack Query cache — not duplicated in a global store |

### Four explicit UI states (every data screen)

**Loading** (skeleton/spinner from `shared/ui`), **Empty** (intentional copy),
**Error** (safe message + retry; Problem Details `type` logged dev-only),
**Success**. No ambiguous combined flags — use `isPending`/`isError`/`isSuccess`.

---

## Money, dates & compliance in the UI (binding)

- **Money is integer minimum currency units** end-to-end; format for display only
  at the view edge via a `shared/lib` formatter (or `<Money>` primitive). Never do
  float math on amounts. Keep `amount` / `charge_amount` / `processing_fee` distinct.
- **Datetimes from the API are UTC; display in JST** (ADR 0012). Calendar dates
  (`due_date`) are already JST.
- **No card PAN/CVV ever in frontend code** — payment capture is the gateway's
  hosted iframe/redirect only (ADR 0010). The widget handles tokens/session ids,
  never raw card data.
- **No secrets** (gateway keys, JWT secrets) in source or build output.

---

## Design patterns

| Pattern | Where |
| --- | --- |
| **Hook + View** | `features/{f}/hooks` (logic) + `features/{f}/ui` (presentational) |
| **Entity module** | `entities/{r}/` |
| **Query key factory** | `query-keys.ts` (hierarchical, typed; no string literals in features) |
| **Mapper purity** | `mapper.ts` (pure, unit-tested) |
| **Barrel public API** | `index.ts` |
| **Problem Details mapping** | `shared/api/errors.ts` (single parse path) |
| **Provider stack** | `app/providers.tsx` (one composition root) |
| **Fail-closed auth gate** | `app/auth-gate.tsx` |
| **Forms** | React Hook Form + Zod (UX); destructive submits use a `ConfirmDialog` from `shared/ui` |

### Forbidden anti-patterns (reject)

`useEffect`+`fetch` for server data · prop-drilling server data 3+ layers · global
pub/sub · storing API responses in `useState` · class components · **default exports** ·
business rules in `shared/ui` · string query keys in features · `dangerouslySetInnerHTML`
without sanitization + Issue · auth token in `localStorage` without ADR · raw
color/spacing/type literals · Tailwind arbitrary values (`p-[13px]`, `text-[#fff]`) ·
inline `style` with design literals · feature-local `<button>`/`<input>` styling ·
stories under `features/`/`pages/`.

---

## TypeScript strictness

`tsconfig` minimum: `strict`, `noUncheckedIndexedAccess`, `noImplicitOverride`,
`exactOptionalPropertyTypes`, `verbatimModuleSyntax`, `moduleResolution: bundler`,
`isolatedModules`, `noFallthroughCasesInSwitch`, `forceConsistentCasingInFileNames`.

- **`any` forbidden** — use `unknown` and narrow.
- `@ts-expect-error`/`@ts-ignore` require an Issue/ADR id in the comment.
- No `!` non-null assertion without an invariant comment.
- `interface` for component props; `type` for unions/mapped types.
- `satisfies` for const config (query defaults, route maps).
- **Branded IDs** in `ids.ts` — no bare `string` for resource ids across layers.
- Exhaustive `switch` on discriminated unions.
- Env vars validated once in `shared/config/env.ts` (Zod).

---

## Design system & theming (zero tolerance)

All visual values live in **`shared/ui/theme/`**. Components/features **never**
hard-code margins, paddings, colors, fonts, backgrounds, radii, shadows, z-index.

- One file per complete theme: `shared/ui/theme/themes/{name}.css` (Tailwind v4
  `@theme` + CSS custom properties; full token set).
- `active.css` is a single pointer (`@import './themes/default.css'`). **Switching
  themes changes only `active.css`** — no component PR. `app/providers.tsx` imports
  `theme/index.css` once; features/pages never import theme CSS.
- Components use **semantic Tailwind utilities only** (`bg-surface`, `text-primary`,
  `p-inline-md`, `rounded-md`, `font-sans`). **No** `[…]` arbitrary values; **no**
  hex/rgb/hsl/px literals in `.tsx`/`.ts`. `tokens.ts` (optional) mirrors CSS vars —
  not a second source of truth.
- `shared/ui` layering: `theme/` (no React) → `primitives/` → `components/`. Features
  import the `shared/ui` barrel only; primitives are swappable when their public
  contract (props in / callbacks out) stays stable.

---

## Storybook & component contracts

- **Required** for every exported `shared/ui` primitive and composed component;
  **forbidden** under `features/`/`pages/`/`entities/`.
- Colocate `Button.tsx` + `Button.stories.tsx`. Each story file header documents the
  **In / Out / Does not** contract. Cover default, disabled (if any), and each variant.
- `npm run build-storybook` is part of CI `check`.

---

## API & data access

- Single `apiClient` in `shared/api/client.ts` with typed `get/post/patch/delete`;
  attaches auth per API policy; parses JSON; throws **`AppError`** from Problem
  Details on 4xx/5xx. **No domain logic** — transport only.
- TanStack Query defaults in `app/providers.tsx` (`staleTime`, retry only on
  retryable `AppError`, mutations `retry: false`). `queryFn` calls the mapper before
  returning to cache. Export hooks with explicit return types
  (`UseQueryResult<ReceivedInvoice, AppError>`).

---

## State management

| State | Tool | Location |
| --- | --- | --- |
| Remote server data | TanStack Query | `entities/*/queries.ts` |
| Writes | TanStack mutations | `entities/*/mutations.ts` |
| URL / shareable | React Router | `pages/` + feature hooks reading `searchParams` |
| Form draft | React Hook Form | feature ui + hooks |
| Ephemeral UI | `useState` | feature ui |
| Auth/role flag, current org, locale | Context in `app/`/`shared` only | minimal; details from API |

---

## Security

The browser is a **hostile context**.

| Topic | Rule |
| --- | --- |
| Card data | **No PAN/CVV in frontend** — gateway hosted iframe/redirect only (ADR 0010) |
| Secrets | Never in repo; only public `VITE_*` in frontend env |
| Auth tokens | Prefer httpOnly cookies; no token in `localStorage` without ADR |
| XSS | No `dangerouslySetInnerHTML` without DOMPurify + Issue |
| Links | `rel="noopener noreferrer"` on `target="_blank"` |
| Open redirects | Validate post-login redirect against an allowlist |
| Dependencies | `npm audit` in CI; block high/critical on `main` |
| PII / tokens in logs | Never in production logs |
| RBAC UI | Hide/disable by API-exposed capability — UI gating is UX only; API enforces |
| Tenant | UI never selects `organization_id` for scoping — server resolves it (ADR 0018) |
| Fail closed | 401 → login; 403 → forbidden; never silent unauthenticated mutations |

---

## Testing

| Level | Tool | Required when |
| --- | --- | --- |
| Unit | Vitest | `mapper.ts`, `query-keys.ts`, pure `lib/` — every entity |
| Integration | Vitest + Testing Library + MSW | every feature PR |
| Contract | MSW vs OpenAPI | endpoint touched |
| E2E | Playwright | critical journeys (Phase 2+ + Issue) |

- Query by **role/label/accessible name** — not class/`data-testid` unless no a11y hook.
- `userEvent.setup()`; wrap with `createTestQueryClient()` (retries off).
- MSW handlers match OpenAPI; factories build **models**, not DTOs.
- No mocking child components to skip integration; no full-page snapshots.
- **Every new feature ships ≥1 feature-hook test** (MSW): primary query loads + each
  mutation drives its observable outcome (refetch/success/Problem Details). Missing →
  merge blocked. Bug fixes include a regression test unless an Issue waives.

---

## Accessibility / performance / observability

- **WCAG 2.2 AA**; `eslint-plugin-jsx-a11y` errors fail CI; focus management on
  route change and modal open/close; form errors via `aria-describedby`.
- Route-level code splitting (`React.lazy`); virtualize long lists (>100 rows).
- Dev-only structured logging behind `import.meta.env.DEV`; Query Devtools dev-only.

---

## i18n

All user-facing text via `t(key)` from `shared/i18n`; **no hardcoded ja/en**.
Full design: [`../explanation/i18n.md`](../explanation/i18n.md). Errors localize by
mapping problem `type`/`code` to catalog messages; the API is not translated.

---

## Admin vs widget

| App | Purpose | Shared |
| --- | --- | --- |
| **Admin SPA** | Invoice/vendor/payment management, settings, audit | `shared/`, `entities/` |
| **Widget** | Embeddable payment form (token-gated, `data-locale`) | `shared/api`, `shared/ui`, relevant `entities/` |

The widget is a separate Vite entry; it must not import admin-only feature modules,
must use the gateway's hosted card capture (no PAN), and derives its org from its
signed token (multi-tenancy.md §6).

---

## Commands & CI

```bash
npm install --prefix frontend
npm run dev --prefix frontend        # Vite on host port 5190
npm run check --prefix frontend      # type-check + lint + format + test + build-storybook
```

CI on frontend PRs: `npm ci` → `npm run check` → `npm run knip` → `npm audit --audit-level=high`.
ESLint encodes boundaries (`features/**` ⊄ `shared/api/generated/**`; `shared/ui/**` ⊄
`entities|features`; sibling `entities` isolation) and forbids Tailwind arbitrary values
outside `shared/ui/theme/`.

---

## Related

- Self-review: [`../review/frontend.md`](../review/frontend.md)
- ADR: [`../adr/0019-frontend-architecture.md`](../adr/0019-frontend-architecture.md)
- i18n: [`../explanation/i18n.md`](../explanation/i18n.md)
- Pages/roles: [`../explanation/pages.md`](../explanation/pages.md)
- Naming: [`./naming-conventions.md`](./naming-conventions.md)
- Reference implementation: `../nene-records` `frontend/`
