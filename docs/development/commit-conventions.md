# Commit Conventions — NeNe Payout

Inherits [NENE2 commit conventions](https://github.com/hideyukiMORI/NENE2/blob/main/docs/development/commit-conventions.md).

## Format

```
<type>(<scope>): <日本語の説明> (#<issue番号>)

[オプションの本文 — 変更理由・トレードオフ・後続作業]
```

- `type` / `scope`: **English**
- description / body: **Japanese**
- Include `(#issue)` in subject line

## Types

| type | Use |
| --- | --- |
| `feat` | New feature |
| `fix` | Bug fix |
| `docs` | Documentation only |
| `refactor` | Code change without feature/fix |
| `test` | Add or modify tests |
| `build` | Dependencies, build config |
| `ci` | CI configuration |
| `chore` | Maintenance |

## Scope examples

| scope | Target |
| --- | --- |
| `received-invoice` | ReceivedInvoice domain |
| `vendor` | Vendor domain |
| `payment` | Payment / gateway domain |
| `widget` | Embeddable widget |
| `auth` | Authentication |
| `admin` | Admin UI |
| `openapi` | OpenAPI docs |
| `adr` | Architecture decision records |

## Examples

```
feat(payment): Stripe アダプターを実装する (#12)
feat(vendor): 仕入先 CRUD エンドポイントを追加する (#8)
fix(webhook): 署名検証が空ボディで失敗する問題を修正する (#15)
docs(adr): 決済ゲートウェイアダプターパターンの ADR を追加する (#10)
```
