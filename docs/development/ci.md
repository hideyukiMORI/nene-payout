# CI / セキュリティ自動化

NeNe Payout の継続的インテグレーションと依存・シークレットの安全性に関する方針。
ワークフロー定義は `.github/workflows/`、Dependabot 設定は `.github/dependabot.yml`。

## CI ゲート（`.github/workflows/ci.yml`）

PR と `main` への push で以下を実行する。全ジョブ green が**マージの前提**。

| ジョブ | 内容 | ローカル同等コマンド |
| --- | --- | --- |
| `backend` | PHP 8.4 セットアップ → NENE2 を兄弟ディレクトリに checkout → `composer install` → `composer check`（`test` / `analyse` / `cs` / `openapi`） | `composer check` |
| `frontend` | Node 22 → `npm ci` → `npm run check`（type-check / lint / format / test） | `npm run check --prefix frontend` |
| `secret-scan` | gitleaks による秘密情報スキャン | — |

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
- PR・push ごとに **gitleaks** が履歴を走査する。誤検知は `.gitleaks.toml` の
  `allowlist` で個別に許可する（コミットされた実シークレットは即時失効・ローテーション）。
- カード番号（PAN）は SAQ-A 方針によりシステムに保存・通過させない（ADR 0010、
  `docs/explanation/payment-compliance.md`）。

## 後続（未整備）

- CD（ステージング/本番デプロイ自動化）
- OpenAPI からの型生成・契約テストの CI 組み込み
- 決済ゲートウェイのサンドボックス統合テスト
