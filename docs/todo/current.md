# Current TODO — NeNe Payout

## Status

Phase 0（ガバナンス）完了。Phase 1（コア API）— Vendor / ReceivedInvoice / Payment(stub) /
User / Organization 設定の各スライスと、認証・監査・マルチテナント基盤が完了。フロントは
管理 UI（一覧/詳細/フォーム/ダッシュボード/監査ログ/設定/ユーザー管理）が一通り揃い、
Storybook・knip・i18n 整理も配線済み。残るコア作業は決済の実ゲートウェイ（スライス9以降）だが、
**ADR 0020（Stripe）が Status: Proposed ＝法務サインオフ待ちでゲートが閉じており着手できない**。

7月の主戦線は機能追加ではなく**フリート規約への準拠是正（W1）**。詳細は下記「W1」節。
残る Phase 2 作業は**埋め込みウィジェット**（Issue #122 / PR #123 — コンフリクトで停止中）。

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
- [x] ユーザー管理 API（list/get/create(招待)/update(role)/deactivate、ManageOrganizationSettings・自組織スコープ、監査付き・1トランザクション、password_hash 非露出） — Issue #106
- [x] 自組織設定 API（GET/PATCH /api/v1/organization、admin・自テナント、name のみ更新、監査 organization.updated・1トランザクション） — Issue #110
- [ ] スライス9以降: Stripe 実アダプタ → Webhook（成功/失敗反映） → gateway-settings(+疎通確認) → fee/refund/CB 会計（ADR 0015・税理士サインオフ後）
      🔒 **ブロック中**: ADR 0020 が `Status: Proposed`（法務サインオフ待ち）。Accepted まで着手不可
- [ ] superadmin 横断の組織管理（/api/v1/organizations 複数：list/get/create/update/deactivate。現状 OpenAPI 契約のみ・ハンドラ未実装）
- [ ] 埋め込み決済ウィジェット（2モード：引数渡し支払い／埋め込み請求書管理） — Issue #122 / **PR #123 が open のまま停止**
      🔴 +2,302行・2026-06-18 以降放置・`CONFLICTING`。7月の W1（#159 token codemod・#160/#164 dead class 削除・
      #162 i18n 反転）が同じフロント資産を書き換えたため。放置するほど剥離する

## テスト

- [x] バックエンド UT を全機能・境界値重視で拡充（InputMapper／CapabilityResolver／Ulid／Audit filter／Query UseCase、151 tests） — Issue #46
- [x] ハンドラ層の検証・AuthContext を境界値で網羅（gateway enum／PDF 種別／login 必須／claims、168 tests） — Issue #48

現状の件数（`main`・2026-07-16 に実行して実測）: バックエンド **222 tests / 828 assertions**（`./vendor/bin/phpunit`）
／フロント **181 tests / 44 files**（`npx vitest run`）。いずれも green。
各スライスで mapper／usecase／handler 境界の UT を同 PR に同梱。

> 件数を更新するときは**実際に走らせた出力を貼る**こと。前回（2026-06-17）の「199 / 155」は
> 1ヶ月ぶん陳腐化していた。

## Frontend

- [x] i18n 基盤（frontend scaffold＋shared/i18n、ja/en カタログ一元管理、parity/切替テスト） — Issue #44
- [x] FSD 堅牢スキャフォールド（tooling: strictTypeChecked ESLint＋import 境界＋Tailwind 任意値禁止＋Prettier、shared/config・api・ui テーマ＋primitives、app providers/router/error-boundary/auth-gate、entities/vendor 縦スライス、features/manage-vendors＋MSW フックテスト、pages/vendors、tests render/msw/factories、check green） — Issue #50
- [x] ReceivedInvoice 縦スライス（shared/lib フォーマッタ、entities/received-invoice＋mapper test、features/manage-invoices＋MSW フックテスト、pages/invoices、/received-invoices ルート、ステータスラベル ja/en、check green 35 tests） — Issue #52
- [x] PaymentExecution 縦スライス（entities/payment-execution＋mapper test、queries（status・received_invoice_id フィルタ）＋initiate mutation、features/view-payments＋MSW フックテスト、pages/payments、/payments ルート、決済ステータスラベル ja/en、check green 43 tests） — Issue #54
- [x] ナビゲーション/レイアウトシェル（app/layout AppLayout＝ヘッダー＋サイドナビ＋Outlet、features/switch-locale、app/layout sign-out、認証ルートを AuthGate＋AppLayout 配下にネスト、app.* i18n、AppLayout レンダリングテスト、check green 46 tests） — Issue #56
- [x] フォーム基盤（RHF＋zod）＋Vendor 登録/編集（shared/ui Input/Select/FormField、entities/vendor AccountType、features/manage-vendors model/vendor-form＝zod（バックエンド VendorInputMapper と一致）＋VendorForm/CreateVendorForm/EditVendorForm、/vendors/new・/vendors/:id/edit、一覧に新規＋編集リンク、admin.vendors.form.* i18n、schema＋form テスト、check green 61 tests） — Issue #58
- [x] ReceivedInvoice 登録/編集フォーム（features/manage-invoices model/invoice-form＝zod（ReceivedInvoiceInputMapper と一致、文字列フィールド→整数変換）＋仕入先 Select＋税区分 useFieldArray、InvoiceForm/CreateInvoiceForm/EditInvoiceForm、/received-invoices/new・/:id/edit（編集は pending のみ）、admin.receivedInvoices.form.*／税率ラベル i18n、schema＋form テスト、eslint allowNumber 有効化、check green 76 tests） — Issue #60
- [x] CI 基盤（GitHub Actions: backend composer check＋NENE2 兄弟 checkout／frontend npm run check／gitleaks、Dependabot composer・npm・actions、docs/development/ci.md）— Issue #62（PR 上で CI green を確認）
- [x] 決済開始 UI（features/initiate-payment＝gateway Select フォーム＋PayInvoicePanel、pending 請求書 → useInitiatePayment → gateway_redirect_url へ遷移、/received-invoices/:id/pay、一覧 pending 行に導線、ゲートウェイ名/決済開始 i18n、schema＋form テスト、check green 81 tests） — Issue #69
- [x] PDF アップロード UI（shared/api postForm＝multipart、entities useAttachReceivedInvoicePdf、features/upload-invoice-pdf＝pdf-file 検証（application/pdf・soft cap）＋UploadInvoicePdfForm/Panel、/received-invoices/:id/pdf、一覧の全行に導線、pdf.* i18n、検証＋form テスト、check green 88 tests） — Issue #76
- [x] 詳細画面（shared/ui DetailList＋shared/lib formatDateTime（JST 表示）、VendorDetailView／InvoiceDetailView（仕入先名を子で解決・税区分表示）／PaymentDetailView、/vendors/:id・/received-invoices/:id・/payments/:id、一覧から詳細リンク、詳細タイトル/フィールド i18n、各詳細の MSW happy-path テスト、check green 94 tests） — Issue #78
- [x] ログイン画面と認証フロー（entities/session、login/me、AuthGate、トークン保持） — Issue #86
- [x] 監査ログ画面（features/view-audit-logs、/audit-logs、ManageOrganizationSettings ガード） — Issue #90
- [x] ダッシュボード画面（features/view-dashboard、/dashboard、各リソースへの導線） — Issue #92
- [x] ロールベースのナビ／ルート出し分け（roleHasCapability、RequireCapability、AppLayout ナビ capability フィルタ、/forbidden） — Issue #104
- [x] ユーザー管理 UI（entities/user、features/manage-users＝招待/一覧/詳細/ロール変更/無効化、/users、ManageOrganizationSettings ガード） — Issue #108
- [x] 組織設定（設定）画面（entities/organization、features/manage-organization-settings＝組織名の表示・編集、/settings、ManageOrganizationSettings ガード） — Issue #112
- [x] FSD 横展開の続き＝完了（ダッシュボード/設定/監査ログ画面、i18n 未使用キー整理 #96、Storybook #98、knip 導入 #94）
  - ⚠️ **#94 は knip を「導入」しただけで `check` に配線されておらず、2026-07-15 の #176 まで一度も走っていなかった**。
    本書は 1ヶ月間これを「完了」と書いていた（#181 で是正）。導入 ≠ 実行、を教訓として残す
- [x] ツール更新: ESLint v10（#100）／Vite v8（#102）

各スライスで該当エンティティのマイグレーション＋OpenAPI＋テストをセットで追加。

**すべて `docs/explanation/payment-compliance.md`（binding）に拘束され、`docs/review/compliance.md` を通すこと。**
**Issue を立ててから着手すること（Issue 駆動）。**

## W1 — フリート規約への準拠是正（2026-07）

7月の主戦線。機能追加ではなく「規約と現物の乖離を消す」作業。**マージ済み**:

- [x] token を localStorage → sessionStorage へ即是正 — #152 / PR #153
- [x] `@hideyukimori/nene2-client` transport 採用（Stage2b） — #154 / PR #155
- [x] token vocabulary codemod 適用（nene2-tokens codemod-map v1.0.0） — #156 / PR #159
- [x] dead class 是正 第1波（text-primary/text-muted 17箇所） — #157 / PR #160
- [x] dead class 是正 第2波（text-body×34／text-heading×2） — #163 / PR #164
- [x] known-utility lint 配線（nene2-standards ^1.0.0） — #158 / PR #161
- [x] i18n 型権威カタログを ja へ反転（規約 04 I18N-8 のパイロット） — #162
- [x] en.ts の parity 担保を satisfies へ — #167 / PR #168
- [x] X-Authorization フォールバック受け口を opt-in 有効化 — #165 / PR #166
- [x] token storage key を `nene_payout_token` へ是正 — #171 / PR #172
  - 現物量の実測: アンダースコア形 8リポ vs **ハイフン形は payout の1本のみ**＝規約が正しく payout が外れ値
- [x] 型権威が en のままという嘘の docstring を是正 — #173 / PR #174
- [x] `check` に knip を追加し dead code 検出器を有効化 — #175 / PR #176

**未完（ブロッカー付き・payout 単独では閉じない）**:

- [ ] 🔒 **exemplar アンカー3本（I18N-6 / I18N-20 / I18N-22）＝植えない判断で W1 送り** — Issue #169（close 済・調査は同 Issue のコメント）
  - 3本とも**指し先が当の条文に未準拠**。当初の根本原因は `@hideyukimori/nene2-i18n` の未 publish だった
  - 新事実: `check:exemplars` は `body.includes('[' + anchor + ']')` の**単純部分一致で指し先の内容を照合しない**
    ＝**アンカーは目印であって検出器ではない**。植てば green になるが実質を伴わない
  - 規約 `04-i18n.md:167` に現状注記済み（**red のままが正**・red のまま批准 MUST NOT）
  - **2026-07-16 15:00 更新 — publish されたのでブロッカーの中身が変わった**（自分で `npm view` / `npm pack` して実測）:
    - `@hideyukimori/nene2-i18n@0.1.0` が **publish 済み**（`time.created` = `2026-07-16T05:46:12Z` ＝ 14:46 JST）。
      **`private: true` と npm E404 は解消**
    - `exports` は **`.` のみ**で、規約が指定する **`./testing` サブパスは依然存在しない**（fleet 規約 04 §0 の「W0b 目標形」注記どおり）
    - ただし **`expectCatalogParity` / `checkCatalogParity` は root から import 可能**（`index.d.ts` 実測）。
      → **I18N-20 は「準拠手段が無い」から「規約が指す import 経路と実体がズレている」へ後退**。実体は使える
    - **`resolveLocale` は export されていない**（root の公開 API は `createTranslator` / `checkCatalogParity` /
      `expectCatalogParity` と型のみ）。→ **I18N-6 は依然ブロック**（package が責務を引き取れる状態にない・W0b）
  - 残ブロッカー: **W0b（`./testing` サブパスと `resolveLocale` の提供）**。手番は fleet-tooling 側
- [ ] 🔴 **I18N-22 違反が構造として現存** — `translate.ts:23` の `messages[key] ?? en[key]` が規約の ❌ 負例と**構造同一**
      （沈黙フォールバック・フォールバック先が権威 ja でなく en）。`[X]` アンカー自体が無く**どのゲートにも掛からない**
  - ただし **今日この経路は発火しない**（`en.ts` が `Record<MessageKey, string>` で検査され、`locales.test.ts` が
    ja/en のキー集合を相互に固定しているため全キーが自カタログで解決する）＝**潜在的違反であり実害は出ていない**。
    経緯は `translate.ts` の docstring に記載済み（#174）
- [ ] 🔴 **`validate:themes` が機械的に緑到達不能** — 実測 exit 1・3 errors
      （`active.css`/`index.css` の pragma 欠落、`themes/default.css` の contract キー **23個**欠損）。
      「no mechanical repair」＝道具側の課題として fleet#16 起票済み
- [ ] `use-dashboard` の `hooks/` → `model/` 移設（規約 05:916 が定める・W1 で実施）

### コンプライアンス follow-up（ゲート付き）
- 手数料・返金・チャージバックの会計モデル ＋ 税理士/会計士サインオフ（ADR 0015 → follow-up ADR）後に実装
- ローンチ決済ゲートウェイ選定 ADR（認可・契約済みの主体 — ADR 0009）: **ADR 0020（Stripe・SAQ-A ホスト型）を Status: Proposed で起草済み（Issue #120）**。法務サインオフ → Accepted 後にスライス9（Stripe アダプタ→Webhook→gateway-settings）着手可

## Handoff

Repository: `hideyukiMORI/nene-payout`
Local path: `/home/xi/docker/nene-payout`
Port lane: 90** (API: 9000, Frontend: 5190, MySQL: 3400, phpMyAdmin: 9001) — fixed/unique, see docs/development/local-ports.md

次の焦点: **本命（スライス9＝Stripe）は ADR 0020 の法務サインオフ待ちで着手不可**。
W1 の残りは **W0b 待ち**（`./testing` サブパスと `resolveLocale` の提供・fleet-tooling 側）で、
**payout 単独で動かせる玉は少ない**。手番が来ているのは **Dependabot 12本**の処理
（実測: patch/minor **8** ＋ major **4** ＝ #130 typescript 5.9→7.0 / #128 @types/node 22→26 /
#124 actions/checkout 6→7 / #132 actions/cache 5→6）。段階計画は #184、major は #185 / #186。
**実施は (c) レーンの素振り声掛けが済んでから**（統合リナ裁定・重複回避）。
PR #123（ウィジェット）のコンフリクトは 2026-07-16 に解消し **マージ済み**（widget 本体が main 入り・デモページは別途）。
フロントの管理 UI 横展開は一巡済み。

**Wave G 先鋒＝CSS ゲート起動（2026-07-16 夜・完了）**: #188 / **PR #189 マージ**。
`@hideyukimori/nene2-standards` の stylelint 2枚組を `check` に配線し、payout の CSS 違反0・
変異 assert（stylelint／scan-coverage 両方で赤）まで実証した。意匠・CSS は無改変。
- **PR #189 が 342 リポ横展開のテンプレート**（統合リナ裁定・stylelint 配線・CI は `npm run check` 経由で自動ゲート）。
  横展開先（invoice / vault / field / origin）は **fleet 主導**＝payout 単独では動かさない。
- nene2-check conformance **CLI** の fail-closed 緑化は **Wave G 対象外**に確定（12キーが W0a skeleton unknown＝
  検査器がツール側で未実装・gate-integrity は canonical eslint 全断片＝ガバナンス移行を要する）。
  **W0a（fleet の12検査器実装）完了後の別 Wave**。

他リナ待ちで、こちらから動かさないもの（2026-07-16 に統合リナと同期済み）:
- **#159 の byte 一致の再測**: 道具（fleet PR#50＝namespace 表導出）が施主承認待ちで未マージ。
  **マージ後に統合リナから合図が来る。それまで再測不要**
- **W1 素振り (c)**: payout 単独では 1/6 で閉じない（6リポの調整は統合リナ）
- **Q1: overlay token の置き場**（デモページの `style-prop-css-vars-only` 5 errors）: fleet#16／契約 v2 と絡む
  フリート token 設計のため**施主判断に上げ済み**。回答まで `WidgetDemoPage` は #123 から外したままが正
- **`.claude/worktrees` 385M の実体掃除**: squash 済み #161 との内容照合を統合リナが実施。**触らない**

既知の未処理（コード外）:
- Issue #170: `NENE_PAYOUT_API_URL` の正本が無くポート変更に Vite proxy が追随しない — **施主判断で記録のみ・実装しない**
- `.claude/worktrees` の未 ignore — #179 / PR #180 で是正。**同じ穴がフリート17リポ**にあり統合リナが横断で引き取り済み（issues 起票済）
- 同じ .gitignore の穴が**フリート17リポに存在**（統合リナ案件）

Last updated: 2026-07-16 (棚卸し: 6/17 以降の 27コミット＝7月の W1 全12本、knip「完了」の嘘の是正、
ウィジェット #122/#123 の欠落補充、テスト件数を実測値 222/181 へ更新 — Issue #181。
同日 15:00 追記: nene2-i18n@0.1.0 の publish を実測し I18N-6/20 のブロッカーを W0b へ絞り込み、
他リナ待ちの4件を明記 — 統合リナと同期。
同日 18:00 追記: Wave G 先鋒＝CSS ゲート起動 完了（#188 / PR #189 マージ）。#189 が 342横展開の
テンプレ・conformance CLI は W0a 後の別 Wave — Issue #190・統合リナと同期)
