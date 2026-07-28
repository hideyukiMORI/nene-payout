# Dependency vulnerability gate (frontend)

Every PR runs a dependency audit as a **merge gate**. This document says what the gate is,
how an exception is granted, and what is currently excepted.

- Config: [`frontend/audit-ci.jsonc`](../../frontend/audit-ci.jsonc) (the file itself carries
  the reasoning for each entry — keep the two in sync)
- Command: `npm run audit --prefix frontend`
- CI: the `Audit (fail on high/critical)` step of the `Frontend (npm run check)` job in
  [`.github/workflows/ci.yml`](../../.github/workflows/ci.yml)

## The gate

`audit-ci` fails the build on any **high** or **critical** advisory that is not explicitly
allowlisted. Moderate and below do not fail (they are still reported).

We use `audit-ci` rather than bare `npm audit --audit-level=high` for one reason: **`npm audit`
has no way to record a reasoned exception.** Without one, the only ways past a
not-yet-fixable advisory are to lower the severity threshold or drop the step — both of which
blind the gate to *everything*, not just the advisory in question.

Until 2026-07-29 payout had **no dependency-audit gate at all**: the 2026-07-21 laneD acceptance
verified `npm audit --audit-level=high` rc=0 by hand, but nothing was wired into `check` or CI.
Eight days later the same command returned rc=1 with 8 high advisories and nothing caught it.
A hand-run check is not a gate — that is why this step exists.

## Rules for an exception

1. **Per advisory id, never per severity.** Allowlist `GHSA-…`; do not raise `--audit-level`
   and do not set `high: false`. A new advisory must still fail the build the day it lands.
2. **The reason must be measured, not assumed.** State why the vulnerable code path does not
   exist *in this codebase*, and how that was checked (a grep, a build artifact, a config).
   "We probably don't use that" is not a reason.
3. **Every entry has an expiry** and a named condition that removes it (an upgrade wave, an
   upstream fix). An expired entry is a task — re-argue it in a PR; do not extend it by reflex.
4. **Prefer the fix.** If a patched version exists in a range we can take, take it. An
   exception is only for "no fix exists that we can adopt".

## Current exceptions

| Advisory | Package | Why it does not apply here | Expires |
| --- | --- | --- | --- |
| [GHSA-qwww-vcr4-c8h2](https://github.com/advisories/GHSA-qwww-vcr4-c8h2) | `react-router` (7.12.0–8.2.0) | payout's console is a **static SPA built by Vite** (`vite build`, no SSR entry) served from `public_html/`. `src/main.tsx` mounts with `createRoot`; there is no `hydrateRoot` / `renderToString` / `renderToPipeableStream` in `src/`. Routing uses the **declarative** `<BrowserRouter>` + `<Routes>` API (`src/App.tsx`, `src/app/router.tsx`) — `createBrowserRouter` and data routers are not used, so **no route-level `action:` / `loader:` exists at all**, and there is no RSC / server runtime (`react-router/server`, `@react-router/dev`, `'use server'`, `createStaticHandler` — 0 hits). The advisory's attack path (a server executing a route action before returning 400) has no counterpart here. Measured 2026-07-29 on this tree. | **2026-08-31** |

There is **no fix available in the 7.x line**: `react-router-dom` ends at 7.18.1, and the fix
lands in `react-router` v8 (≥ 8.2.1) — a different package and a breaking upgrade. The exception
is removed by the **react-router v8 migration wave** (bundled with the NENE2 RR8 re-evaluation).

## Pins are time-limited, not fixes

Pinning a transitive dependency to dodge an advisory buys time; it does not end the problem,
because **the pinned version can itself fall inside a later advisory**. payout demonstrated this:
the 2026-07-21 `overrides` pinned `brace-expansion@1: 1.1.16 / @2: 2.1.2 / @5: 5.0.7`, and on
2026-07-29 [GHSA-mh99-v99m-4gvg](https://github.com/advisories/GHSA-mh99-v99m-4gvg) (`<= 5.0.7`)
made two of those three pins vulnerable, dragging `minimatch`, `eslint-plugin-jsx-a11y` and
`@hideyukimori/nene2-standards` in with them. The fix was to stop pinning per major and take a
range (`"brace-expansion": "^5.0.8"`). **Prefer ranges; revisit pins.**

## Fleet note

The reference implementation is **contact** (contact #524 / PR #525, 施主 GO 2026-07-29); payout
copies its shape. The allowlist entry above was **re-measured against payout's own tree** before
being copied — payout's evidence is in fact stronger than contact's (declarative router, so no
data-router `action`/`loader` API is in use at all). Copying an exception without re-measuring is
exactly the failure mode the rules above exist to prevent.

## Related

- [`coding-standards.md`](./coding-standards.md) — the wider merge-gate set
- [`ci.md`](./ci.md) — CI / Dependabot / secret scanning
