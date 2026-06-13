# NeNe Payout

[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](./LICENSE)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://www.php.net/)
[![Status: Phase 0](https://img.shields.io/badge/status-phase%200-orange)]()

**Pay vendor invoices by credit card — self-hosted on NENE2.**

**NeNe Payout** lets businesses pay received invoices (from vendors, suppliers, and
contractors) by credit card, while keeping all payment data on their own server.
An embeddable widget integrates the payment flow into any existing system.
Built on [NENE2](https://github.com/hideyukiMORI/NENE2), runs on shared hosting or Docker.

> **Separate product.** Payout does **not** issue quotes/invoices
> ([`nene-invoice`](https://github.com/hideyukiMORI/nene-invoice)),
> reconcile bank deposits ([`nene-clear`](https://github.com/hideyukiMORI/nene-clear)),
> or archive received documents ([`nene-vault`](https://github.com/hideyukiMORI/nene-vault)).
> See [ADR 0002](./docs/adr/0002-separate-from-sibling-products.md).

## Domain (binding)

| Product | Repository | What it does |
| --- | --- | --- |
| **NeNe Invoice** | `nene-invoice` | Quote, invoice, payment management — 見積・請求・入金管理 |
| **NeNe Clear** | `nene-clear` | Payment reconciliation & dunning — 入金消込・督促管理 |
| **NeNe Vault** | `nene-vault` | Received-document archive — 受取書類の保存・検索 |
| **NeNe Payout** | `nene-payout` (this) | Vendor invoice payment via credit card — 受取請求書のカード払い実行 |

## Goals

- **Receive and register** vendor invoices (PDF/image upload or structured input)
- **Pay by card** — credit card charge triggers transfer to vendor bank account via payment gateway
- **Embeddable widget** — drop into any existing system with a single script tag
- **Payment gateway adapters** — swap between gateways via admin panel
- **Self-hosted OSS** — MIT; Tier A shared hosting or Tier B Docker/VPS
- **Full data ownership** — all invoice and payment data stays on your server
- **Optional links** — HTTP reference to Invoice/Clear entities; **no shared DB**

## Documentation (read first)

| Topic | Document |
| --- | --- |
| **Scope contract (GOAL / DO / DON'T)** | [`docs/explanation/scope-contract.md`](./docs/explanation/scope-contract.md) |
| **Payment / legal / tax compliance (binding)** | [`docs/explanation/payment-compliance.md`](./docs/explanation/payment-compliance.md) |
| **Multi-tenancy (binding)** | [`docs/explanation/multi-tenancy.md`](./docs/explanation/multi-tenancy.md) |
| **Audit logging (binding)** | [`docs/explanation/audit-logging.md`](./docs/explanation/audit-logging.md) |
| **i18n / message catalogs (binding)** | [`docs/explanation/i18n.md`](./docs/explanation/i18n.md) |
| **Domain boundary** | [`docs/explanation/scope-boundary.md`](./docs/explanation/scope-boundary.md) |
| **Product vision** | [`docs/explanation/product-vision.md`](./docs/explanation/product-vision.md) |
| **Requirements** | [`docs/explanation/requirements.md`](./docs/explanation/requirements.md) |
| **Canonical terms** | [`docs/terms.md`](./docs/terms.md) |
| **Agents** | [`AGENTS.md`](./AGENTS.md) |
| **Roadmap** | [`docs/roadmap.md`](./docs/roadmap.md) |

## Quick start (Docker)

```sh
cp .env.example .env
docker compose up
```

## Local port allocation (90 lane — fixed, unique)

| Service | Host port | Env var |
| --- | --- | --- |
| PHP API | **9000** | `NENE_PAYOUT_PORT` |
| phpMyAdmin | **9001** | `NENE_PAYOUT_PHPMYADMIN_PORT` |
| Vite dev server | **5190** | `NENE_PAYOUT_FRONTEND_PORT` |
| MySQL | **3400** | `NENE_PAYOUT_MYSQL_PORT` |

Fixed and unique to avoid collisions with sibling apps. Do **not** reuse sibling ports.
See [`docs/development/local-ports.md`](./docs/development/local-ports.md) and the
[nene-playbook port registry](https://github.com/hideyukiMORI/nene-playbook).
