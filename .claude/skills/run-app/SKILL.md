---
name: run-app
description: Launch the NeNe Payout app locally (Docker API + MySQL + Vite frontend) and view it in a browser, or drive it headless for screenshots. Use when asked to run, start, open in a browser, or screenshot the Payout app.
---

# Run NeNe Payout locally

Two layers: the **Docker backend** (PHP API + MySQL + phpMyAdmin) and the **Vite
frontend** dev server. For a real login you need both; for a quick UI look you can
run the frontend alone with mocked `/api`.

## Ports — the 90 lane (binding)

The authority is [`docs/development/local-ports.md`](../../../docs/development/local-ports.md)
(**binding**; canonical env defaults in [`docs/terms.md`](../../../docs/terms.md) §8).
This skill must never define its own ports — if these two disagree, the binding doc wins.

| Service | URL | Env var |
| --- | --- | --- |
| API | http://localhost:9000 | `NENE_PAYOUT_PORT` |
| phpMyAdmin | http://localhost:9001 | `NENE_PAYOUT_PHPMYADMIN_PORT` |
| Frontend (Vite) | http://localhost:5190 | `NENE_PAYOUT_FRONTEND_PORT` |
| MySQL (host) | localhost:3400 | `NENE_PAYOUT_MYSQL_PORT` |

These are the `.env.example` defaults; local `.env` also wants `DB_ADAPTER=mysql`
and `ORG_SLUG=payout`. The Vite dev proxy target derives from `NENE_PAYOUT_PORT`,
so moving the API port moves the proxy with it (see #170).

> **If a port is already bound**, do not invent an alternate lane in this file.
> Find the squatter (`docker ps --format '{{.Names}}\t{{.Ports}}'`), stop it, and if
> the collision is a real allocation conflict, fix it in the binding doc and the
> [nene-playbook port registry](https://github.com/hideyukiMORI/nene-playbook) — the
> cross-product authority. An earlier version of this skill hard-coded an
> "alternate lane" (9002/9003/5191) for a `nene-field` collision; field later moved
> to its own lane, and this file kept telling people the wrong ports.

## Full stack (real login) — recommended

```bash
cd /home/xi/docker/nene-payout

# 1. Backend: app + mysql + phpmyadmin. Entrypoint runs `composer install` +
#    `migrations:migrate` automatically (NOT seeds).
docker compose up -d

# 2. Wait for the API, then seed the default org + admin user (idempotent).
until [ "$(curl -sf -o /dev/null -w '%{http_code}' http://localhost:9002/health)" = 200 ]; do sleep 2; done
docker compose exec -T app composer migrations:seed

# 3. Frontend dev server → proxies /api to the API on 9002.
NENE_PAYOUT_FRONTEND_PORT=5191 NENE_PAYOUT_API_URL=http://localhost:9002 \
  nohup npm run dev --prefix frontend > /tmp/vite-dev.log 2>&1 & disown
until [ "$(curl -sf -o /dev/null -w '%{http_code}' http://localhost:5191/)" = 200 ]; do sleep 1; done
```

- **Open** http://localhost:5191 and log in: **`admin@payout.test` / `password`**
  (role `admin`, seeded; overridable via `SEED_ADMIN_EMAIL`/`SEED_ADMIN_PASSWORD`).
  An `admin` user does NOT see the **組織管理 / Organizations** nav item
  (`ManageOrganizations` is superadmin-only) — that is correct RBAC, not a bug.
- Health check: `curl -fsS http://localhost:9002/health` → `{"status":"ok"}`.

### Stop

```bash
pkill -f 'node.*vite'            # frontend
docker compose stop             # backend (keeps data); `down` to remove containers
```

## Frontend only (quick UI look, no backend)

`AuthGate` only checks for a token in `sessionStorage['nene_payout_token']`; the API
is the real authz. So you can inject a token + mock `/api` and see every screen
without standing up MySQL. Locale: `localStorage['nene-payout-locale'] = 'ja'|'en'`
(locale is localStorage and keeps the hyphen form — only the token key is fleet-governed).

```bash
NENE_PAYOUT_FRONTEND_PORT=5191 nohup npm run dev --prefix frontend > /tmp/vite-dev.log 2>&1 & disown
```

## Headless screenshots (agent has no display)

No `chromium-cli` here, but Playwright's Chromium is cached. Use `playwright-core`
with an explicit `executablePath` (skip the bundled-browser download):

```bash
cd /tmp && npm init -y >/dev/null 2>&1 && npm i playwright-core@1.55.0 >/dev/null 2>&1
```

```js
// node /tmp/shot.mjs  — CJS module, import the default then destructure
import pkg from '/tmp/node_modules/playwright-core/index.js'
const { chromium } = pkg
import { existsSync } from 'node:fs'
const exe = [
  '/root/.cache/ms-playwright/chromium-1228/chrome-linux64/chrome',
  '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',
].find(existsSync)
const b = await chromium.launch({ executablePath: exe, args: ['--no-sandbox'] })
const ctx = await b.newContext({ viewport: { width: 1280, height: 860 } })
await ctx.addInitScript(() => localStorage.setItem('nene-payout-locale', 'ja'))
const page = await ctx.newPage()
await page.goto('http://localhost:5191/login', { waitUntil: 'networkidle' })
await page.fill('#login-email', 'admin@payout.test')      // real login (full stack)
await page.fill('#login-password', 'password')
await page.click('button[type=submit]')
await page.waitForURL('**/dashboard')
await page.screenshot({ path: '/tmp/fe.png' })
await b.close()
```

For the **no-backend** variant, instead of logging in:
`addInitScript(() => sessionStorage.setItem('nene_payout_token','demo'))` and
`ctx.route('**/api/v1/**', r => r.fulfill({ status:200, contentType:'application/json', body: JSON.stringify(mockFor(new URL(r.request().url()).pathname)) }))`.
Return PageEnvelope `{items,limit,offset,total}` for list paths, a `CurrentUserDto`
(`role:'superadmin'` to reveal all nav) for `/auth/me`. DTO shapes live in
`frontend/tests/factories/*` and `src/entities/*/api-types.ts`.

## Gotchas

- Login inputs are `#login-email` / `#login-password` (not `#email`).
- `docker compose restart app` does NOT re-read `.env` `environment:` — use
  `up -d --force-recreate app` after editing `.env`.
- If MySQL grants are wrong (volume initialised under old SQLite `.env`):
  `docker compose down && docker volume rm nene-payout_mysql-data && docker compose up -d`,
  then re-seed.
- React controlled inputs: use Playwright `fill`/`type`, not `eval el.value=…`.
