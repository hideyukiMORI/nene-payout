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
- [x] nene-playbook port registry にポートレーンを登録（→ 90 lane へ移行、Issue #17）
- [x] 支払側コンプライアンス基盤（binding payment-compliance.md, ADR 0008–0015, review/compliance.md） — Issue #5
- [x] NENE2 コーディング規約の binding 化（ADR 0016, nene2-runtime-reference, database-standards, frontend-standards, 既存誤記の訂正） — Issue #7
- [x] 用語一覧を唯一の真実として確立＋タイポ厳禁の厳守ルール化（terms.md, ADR 0017） — Issue #9
- [x] マルチテナント設計（nene-records 方式：リクエスト解決＋RequestScopedHolder、multi-tenancy.md, ADR 0018, ADR 0004 改訂） — Issue #11
- [x] 監査ログ設計（全操作・前後記録・アトミック、audit-logging.md, ADR 0011 格上げ） — Issue #13
- [x] i18n 設計（ja/en メッセージ一覧・即時切替、i18n.md） — Issue #15
- [x] ローカル Docker ポートを衝突しない 90 lane に固定＋ルール化（local-ports.md） — Issue #17
  - [x] nene-playbook レジストリを 90 lane に更新（nene-playbook #8 / PR #9）
- [x] 90 lane のローカル Docker 構成（compose.yaml / .env.example / docker） — Issue #19
- [x] フロント規約を業界最厳格（FSD）に強化（frontend-standards 全面書換, review/frontend, ADR 0019, cursor rule 30） — Issue #22
- [x] 必要 API 洗い出し＋OpenAPI 契約整備（docs/api/endpoints.md, openapi.yaml 全面） — Issue #24

## Next (Phase 1)

- [x] スライス1: NENE2 ランタイム scaffold ＋ `GET /health`（composer check green） — Issue #26
- [x] スライス2: マルチテナント runtime（Organization 解決 OrgResolverMiddleware＋RequestScopedHolder, ADR 0018, organizations migration） — Issue #28
- [x] スライス3: 認証（BearerAuthMiddleware＋LocalBearerTokenVerifier）＋ Role/Capability、login/me、users migration — Issue #30
- [x] スライス4: 監査基盤（Ulid, AuditRecorder, audit_logs migration, GET /api/v1/audit-logs） — Issue #32
- [x] スライス5: Vendor CRUD（list/get/create/update/deactivate、監査付き・1トランザクション、vendors migration） — Issue #34
- [x] スライス6: ReceivedInvoice CRUD（list/get/create/update(pending のみ)/void、監査付き、received_invoices migration） — Issue #36
- [x] スライス7: ReceivedInvoice PDF アップロード（POST /received-invoices/{id}/pdf、ローカル保存・監査） — Issue #38
- [ ] スライス8以降: payment gateway adapter（PaymentGatewayInterface）→ 決済開始/PaymentExecution → Stripe adapter → Webhook

各スライスで該当エンティティのマイグレーション＋OpenAPI＋テストをセットで追加。

**すべて `docs/explanation/payment-compliance.md`（binding）に拘束され、`docs/review/compliance.md` を通すこと。**
**Issue を立ててから着手すること（Issue 駆動）。**

### コンプライアンス follow-up（ゲート付き）
- 手数料・返金・チャージバックの会計モデル ＋ 税理士/会計士サインオフ（ADR 0015 → follow-up ADR）後に実装
- ローンチ決済ゲートウェイ選定 ADR（認可・契約済みの主体 — ADR 0009）

## Handoff

Repository: `hideyukiMORI/nene-payout`
Local path: `/home/xi/docker/nene-payout`
Port lane: 90** (API: 9000, Frontend: 5190, MySQL: 3400, phpMyAdmin: 9001) — fixed/unique, see docs/development/local-ports.md

Last updated: 2026-06-13
