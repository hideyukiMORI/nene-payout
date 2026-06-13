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
- [x] スライス8: 決済ゲートウェイ抽象＋決済開始（PaymentGatewayInterface＋StubGatewayAdapter、PaymentExecution、POST /received-invoices/{id}/payments、GET /payment-executions・/{id}、payment_executions migration） — Issue #40
- [ ] スライス9以降: Stripe 実アダプタ → Webhook（成功/失敗反映） → gateway-settings(+疎通確認) → fee/refund/CB 会計（ADR 0015・税理士サインオフ後）

## テスト

- [x] バックエンド UT を全機能・境界値重視で拡充（InputMapper／CapabilityResolver／Ulid／Audit filter／Query UseCase、151 tests） — Issue #46
- [x] ハンドラ層の検証・AuthContext を境界値で網羅（gateway enum／PDF 種別／login 必須／claims、168 tests） — Issue #48

## Frontend

- [x] i18n 基盤（frontend scaffold＋shared/i18n、ja/en カタログ一元管理、parity/切替テスト） — Issue #44
- [x] FSD 堅牢スキャフォールド（tooling: strictTypeChecked ESLint＋import 境界＋Tailwind 任意値禁止＋Prettier、shared/config・api・ui テーマ＋primitives、app providers/router/error-boundary/auth-gate、entities/vendor 縦スライス、features/manage-vendors＋MSW フックテスト、pages/vendors、tests render/msw/factories、check green） — Issue #50
- [x] ReceivedInvoice 縦スライス（shared/lib フォーマッタ、entities/received-invoice＋mapper test、features/manage-invoices＋MSW フックテスト、pages/invoices、/received-invoices ルート、ステータスラベル ja/en、check green 35 tests） — Issue #52
- [x] PaymentExecution 縦スライス（entities/payment-execution＋mapper test、queries（status・received_invoice_id フィルタ）＋initiate mutation、features/view-payments＋MSW フックテスト、pages/payments、/payments ルート、決済ステータスラベル ja/en、check green 43 tests） — Issue #54
- [ ] FSD 横展開の続き（フォーム（RHF＋zod）登録/編集、決済開始 UI、PDF アップロード UI、ナビゲーション/レイアウトシェル、Storybook、knip、husky/CI 化）

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

Last updated: 2026-06-14 (PaymentExecution frontend slice)
