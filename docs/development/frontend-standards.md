# Frontend Standards — NeNe Payout

**Binding.** How the admin UI and embeddable widget are built, placed, and wired.
Inherits NENE2 `frontend-integration.md`; this file records Payout-specific rules.

## Stack (binding)

- **React + TypeScript + Vite**, package manager **npm**, Node active LTS.
- Source in `frontend/`; **never** in `public_html/`. Build output to
  `public_html/assets/` only (safe-to-serve). `node_modules/` and generated
  assets are git-ignored; commit `frontend/package-lock.json`.
- `frontend/package.json` defines `engines` and `packageManager`, and exposes:
  `dev`, `build`, `type-check`, `lint`, `format`, `test`, and a `check` that runs
  type-check + lint + format (+ test). Verification: `npm run check --prefix frontend`.

## Directory layout (binding)

Group by feature/role; mirror NENE2's starter shape.

```
frontend/src/
  api/            typed API client (one module per resource) + __tests__/
  components/     reusable presentational components + __tests__/
  features/       feature screens (received-invoices, vendors, payments, settings)
  widget/         embeddable payment widget entry (script-tag build)
  shared/i18n/    ja / en message catalogs + I18nProvider (see i18n.md)
  test/           test setup/helpers
```

- Pages/routes follow [`../explanation/pages.md`](../explanation/pages.md)
  (`/admin/*`, `/widget`). Keep routing thin; screens live under `features/`.
- Component files are named after their role (PascalCase component, matching
  file name). Co-locate tests in `__tests__/`.

## Data flow & calling conventions (binding)

```text
Component → typed API client (frontend/src/api) → fetch → NENE2 JSON API
```

- All network calls go through the **typed API client** in `frontend/src/api`.
  Components never call `fetch` directly.
- The API client uses a small typed fetch wrapper; in dev, Vite proxies `/api/*`
  to the Docker backend; base URL is overridable via
  `VITE_NENE_PAYOUT_API_BASE_URL` when needed.
- Errors come back as RFC 9457 Problem Details — the client parses
  `application/problem+json` and preserves typed error handling (incl.
  `validation-failed` `errors[]`).
- Money is **integer minimum currency units** end to end; format for display
  only at the view edge — never do float math on amounts.
- Datetimes from the API are **UTC**; convert to **JST** for display only
  (ADR 0012). Calendar-date fields (`due_date`) are already JST.

## State & components

- Prefer small, typed, presentational components; keep data-fetching in
  feature-level hooks/containers, not deep in leaf components.
- No business logic duplicated from the backend — the server owns invariants;
  the UI validates *format* for UX only.

## Internationalization (binding)

Full design: [`../explanation/i18n.md`](../explanation/i18n.md).

- All user-facing strings go through `t(key)` from `frontend/src/shared/i18n`;
  **no** hardcoded ja/en text anywhere (components, widget, errors, aria-labels).
- `messages/en.ts` is the typed source of truth (`MessageCatalog`); `ja.ts` is
  `Partial` and must be complete for release (key-parity test gate).
- Runtime locale switch (ja / en) is instant + persisted (`localStorage`);
  the API is not translated — UI maps stable error `code`/problem `type` to
  catalog messages.

## Widget (binding)

- The embeddable widget is a separate Vite entry built for `<script>`-tag embed
  with `data-*` attributes; supports CSS-variable customization (color, font,
  border) and modal/inline mode (see roadmap Phase 2).
- The widget uses the gateway's hosted/iframe card capture only — **no PAN** ever
  touches widget code (ADR 0010). No secrets in frontend code or build output.

## Security (binding)

- No secrets, API keys, gateway credentials, or tokens in frontend source or
  built assets.
- Never log card data, tokens, or full Problem Details containing sensitive
  fields.

## Quality tools (binding)

- TypeScript (`tsc --noEmit`), ESLint (with React hooks rules), Prettier, Vitest
  + Testing Library. `npm run check --prefix frontend` must pass before PR.
- TSDoc for exported utilities, hooks, types, and API client helpers; do not
  repeat types.

## Related

- Pages / roles: [`../explanation/pages.md`](../explanation/pages.md)
- Runtime / API errors: [`nene2-runtime-reference.md`](./nene2-runtime-reference.md)
- Compliance: [`../explanation/payment-compliance.md`](../explanation/payment-compliance.md)
