# CI / セキュリティ自動化

NeNe Payout の継続的インテグレーションと依存・シークレットの安全性に関する方針。
ワークフロー定義は `.github/workflows/`、Dependabot 設定は `.github/dependabot.yml`。

## CI ゲート（`.github/workflows/ci.yml`）

PR と `main` への push で以下を実行する。全ジョブ green が**マージの前提**。

| ジョブ | 内容 | ローカル同等コマンド |
| --- | --- | --- |
| `backend` | PHP 8.4 セットアップ → NENE2 を兄弟ディレクトリに checkout → `composer install` → `composer check`（`test` / `analyse` / `cs` / `openapi`） | `composer check` |
| `frontend` | Node 22 → `npm ci` → `npm run check`（type-check / lint / format / coverage+ratchet / knip / stylelint）→ `npm run audit`（依存脆弱性ゲート） | `npm run check --prefix frontend` / `npm run audit --prefix frontend` |
| `secret-scan` | gitleaks による秘密情報スキャン | — |

### 依存脆弱性ゲート（`npm run audit`）

`audit-ci` が high / critical の勧告で**ビルドを落とす**。個別の勧告だけを、理由・実測・
期限・解除条件つきで `frontend/audit-ci.jsonc` に allowlist できる（severity は下げない）。
方針の正本は [`dependency-audit.md`](./dependency-audit.md)。

### NENE2 path dependency の扱い

backend は `composer.json` の path repository `../NENE2` に依存する。CI では
`hideyukiMORI/NENE2`（公開）を**この repo の兄弟ディレクトリに checkout** して
ローカルと同じディレクトリ構成を再現する。NENE2 自身の vendor は不要（依存は
nene-payout 側に推移的に解決される）。

## Dependabot（`.github/dependabot.yml`）

週次で更新 PR を作成する。

- `composer`（`/`）— backend の PHP 依存。NENE2 は path 依存のため対象外。
- `npm`（`/frontend`）— frontend の JS 依存。
- `github-actions`（`/`）— CI で使う Action。

Dependabot PR も通常どおり CI ゲートを通過し、レビュー後にマージする。

## シークレット管理

- `.env` / トークン / 認証情報は**コミットしない**（`.gitignore` で `.env` を除外）。
  公開してよい雛形のみ `.env.example` に置き、値は空にする。
- リポジトリに必要な秘密情報は **GitHub Actions Secrets**（`Settings → Secrets and
  variables → Actions`）に登録し、ワークフローからは `${{ secrets.* }}` で参照する。
  ワークフローのログに値を出力しない。
- **gitleaks** の走査範囲はトリガーによって違う（2026-08-14 実測）。
  **PR・push では直近コミットのみ**、**`workflow_dispatch` / `schedule` では
  `--all` で全履歴**を走査する。したがって **PR が緑でも履歴が検査されたことにはならない**。
  `.gitleaks.toml` を変更したときは `workflow_dispatch` を実行して確かめること。
- 誤検知は `.gitleaks.toml` の **`[allowlist]`（単数形）** で**1件ずつ**許可する。ルール単位・
  パス単位で塞がない（次に来る本物を同じ穴に隠すため）。作法は
  `frontend/audit-ci.jsonc` と同じ **ID / 理由 / 期限 / 解除条件**の4点。
  理由は必ず**現物を測った事実**で書く（コミットされた実シークレットは即時失効・ローテーション）。
- ⚠️ **CI の gitleaks は `gitleaks-action@v3` が固定する版**（2026-08-14 時点で **8.24.3**）で、
  手元の最新版とは挙動が違う。8.24.3 には**無警告で効かない・逆に効く**罠が2つある:
  **①`[[allowlists]]`（複数形）は既定設定の extend 時に適用されない**（パースは通り、何もしない）。
  **②1つの allowlist 内の複数条件は AND ではなく OR**（`condition` 未対応）＝
  `commits` に `paths` を併記すると**絞るどころか例外が広がる**。
  ⇒ **単数形＋条件1本**で書き、**CI が固定している版で検証する**こと。
  詳細と実測は `.gitleaks.toml` 冒頭のコメントにある。
- カード番号（PAN）は SAQ-A 方針によりシステムに保存・通過させない（ADR 0010、
  `docs/explanation/payment-compliance.md`）。

## 後続（未整備）

- CD（ステージング/本番デプロイ自動化）
- OpenAPI からの型生成・契約テストの CI 組み込み
- 決済ゲートウェイのサンドボックス統合テスト
