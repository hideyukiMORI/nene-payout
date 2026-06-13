# Current TODO — NeNe Payout

## Status

Phase 0 — Governance bootstrap complete. Phase 1 (core payment API) ready to start.

## Now (Phase 0) — ✅ Complete

- [x] ADR 0001–0007 作成
- [x] docs/explanation/ 全ファイル完成（requirements, features, pages, glossary, domain-model, product-vision, scope-contract, scope-boundary）
- [x] docs/inheritance-from-nene2.md
- [x] docs/workflow.md
- [x] docs/development/ 全ファイル（coding-standards, naming-conventions, backend-standards, nene2-compliance, commit-conventions, self-review）
- [x] OpenAPI skeleton (docs/openapi/openapi.yaml)
- [x] GitHub Issue #1: Governance bootstrap
- [x] nene-playbook port registry に 89 レーンを登録
- [x] 支払側コンプライアンス基盤（binding payment-compliance.md, ADR 0008–0015, review/compliance.md） — Issue #5
- [x] NENE2 コーディング規約の binding 化（ADR 0016, nene2-runtime-reference, database-standards, frontend-standards, 既存誤記の訂正） — Issue #7
- [x] 用語一覧を唯一の真実として確立＋タイポ厳禁の厳守ルール化（terms.md, ADR 0017） — Issue #9

## Next (Phase 1)

NENE2 runtime scaffold → vendor CRUD → received invoice CRUD → payment gateway adapter → Stripe adapter

**すべて `docs/explanation/payment-compliance.md`（binding）に拘束され、`docs/review/compliance.md` を通すこと。**
**Issue を立ててから着手すること（Issue 駆動）。**

### コンプライアンス follow-up（ゲート付き）
- 手数料・返金・チャージバックの会計モデル ＋ 税理士/会計士サインオフ（ADR 0015 → follow-up ADR）後に実装
- ローンチ決済ゲートウェイ選定 ADR（認可・契約済みの主体 — ADR 0009）

## Handoff

Repository: `hideyukiMORI/nene-payout`
Local path: `/home/xi/docker/nene-payout`
Port lane: 89** (API: 8900, Frontend: 5189, MySQL: 3398, phpMyAdmin: 8901)

Last updated: 2026-06-13
