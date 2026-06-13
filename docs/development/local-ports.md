# Local Docker Ports — Binding Rule

**Status: binding.** The development host runs many NeNe apps in parallel; Docker
port collisions are a recurring problem. NeNe Payout therefore uses a **fixed,
unique local port lane** that does not overlap any sibling app. These ports are
the single source of truth in `docs/terms.md` §8.

## The rule

1. **Fixed & unique.** Payout's local ports are fixed to the **90 lane** and must
   not overlap any sibling app's ports (table below).
2. **Never reuse a sibling's port.** Before adding any new local service/port,
   check the ecosystem allocation below and pick a free number in Payout's lane.
3. **Env-driven, with these defaults.** Ports come from env vars (defaults below);
   `.env.example` ships these defaults. Production does not use these.
4. **One source of truth.** The canonical values live in `docs/terms.md` §8.
   Changing a port updates `terms.md`, this file, `compose.yaml`/`.env.example`,
   and the README in the same PR.

## NeNe Payout lane — `90 lane` (fixed)

| Service | Host port | Env var |
| --- | --- | --- |
| PHP API (HTTP) | **9000** | `NENE_PAYOUT_PORT` |
| phpMyAdmin | **9001** | `NENE_PAYOUT_PHPMYADMIN_PORT` |
| MySQL | **3400** | `NENE_PAYOUT_MYSQL_PORT` |
| Vite dev server | **5190** | `NENE_PAYOUT_FRONTEND_PORT` |

Health check: `curl -fsS http://localhost:9000/health`

> Previously Payout used the `89 lane`; that collides with **NeNe Corpus**
> (`89**`), so it was moved to the free `90 lane`.

## Ecosystem allocation (do not reuse)

Host-port ranges/numbers already taken by sibling apps. `XX**` means the whole
`XX00–XX99` HTTP range is reserved for that app.

| App | HTTP range | Other fixed ports |
| --- | --- | --- |
| NeNe Serve | `80**` | 1080, 3380, 3308, 5180, 6107 |
| NeNe Deal | `81**` | 3310, 5187, 6106 |
| NENE2 | `82**` | 3316 |
| NeNe Clear | `83**` | 5173 |
| NeNe Profile | `84**` | 3409 |
| NeNe Invoice | `85**` | 5185 |
| NeNe Vault | `86**` | 5186 |
| NeNe Concierge | `87**` | 3790 |
| NeNe Suite | `88**` | 3390, 5188 |
| NeNe Corpus | `89**` | 3389, 5271 |
| **NeNe Payout** | **`90**`** | **3400, 5190** (9000 API, 9001 phpMyAdmin) |
| NeNe Records | `180**` | — |

This map is a local convenience copy; the authoritative cross-product registry is
the [nene-playbook port registry](https://github.com/hideyukiMORI/nene-playbook).
When this table and the playbook disagree, the playbook wins — fix this file.

## Related

- Canonical env defaults: [`../terms.md`](../terms.md) §8
- Product identity: [`../adr/0005-product-identity.md`](../adr/0005-product-identity.md)
