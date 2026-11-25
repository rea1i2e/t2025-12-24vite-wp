# ブランチとデプロイ（テンプレ）

`t2025-12-24vite-wp` の既定。案件複製後は URL・Secrets を案件側で埋める。

**develop／main 分離**。

- テスト: **`develop` push** → `deploy.yml` がテーマを FTP（リポジトリ Secrets `FTP_*`）
- 本番: **`main` push** → `deploy-production.yml` がテーマを FTP（Environment `production` の `PROD_FTP_*`）

承認待ちは無い（GitHub Free private）。`main` に入ったテーマ変更は Secrets が揃っていれば本番へ出る。テストへは出ない。

Secrets は手元の `.env.deploy`（gitignore）を `scripts/setup-secrets.sh` で登録する。値はリポとチャットに書かない。`env.deploy.example` が項目の正本。

## ブランチの役割

| ブランチ | 役割 | 環境との同期 |
|----------|------|----------------|
| `{作業者}/{topic}` | 実装線 | 環境には出ない |
| `develop` | テスト取り込み先 | **テスト**（`deploy.yml`） |
| `main` | 本番取り込み先 | **本番**（`deploy-production.yml`） |

## 作業の流れ

1. 作業ブランチで実装 → PR → `develop`
2. `develop` へマージされると `deploy.yml` がテスト FTP
3. テスト確認後、`develop` → `main` の PR をマージ
4. `main` へマージされ、テーマ対象の変更なら `deploy-production.yml` が本番 FTP
5. 文書・レビュー画像・workflow だけの変更は、本番 FTP をスキップする（paths-filter）

`main` へ直接 push／直接 PR しない（緊急時を除く）。

## 本番のゲート

Environment 名は `production`。workflow は `.github/workflows/deploy-production.yml`。

| 制限 | 内容 |
|------|------|
| Deployment branches | `main` のみ |
| Secrets の分離 | 本番は `PROD_FTP_SERVER` / `PROD_FTP_USERNAME` / `PROD_FTP_PASSWORD` / `PROD_FTP_SERVER_DIR`。テストは `FTP_*`。空ならテスト用にフォールバックせず失敗 |
| workflow | `github.ref` が `main` でなければ失敗 |
| 対象ファイル | push では PHP・`src`・`dist`・`functions-lib` 等の変更があるときだけ |
| 手動 | `workflow_dispatch` の `dry_run` 既定 true（接続して差分のみ。アップロードしない）。初回基準は `bootstrap_baseline`（テーマは上げず、本番＝ローカル確認後に `.prod-deploy-baseline.json` だけ置く） |
| 本番直書きガード | ビルド後・FTP 前に `scripts/check-prod-deploy-drift.mjs` が、前回成功時の `.prod-deploy-baseline.json` と本番実体を照合。ズレ・基準なし・読取失敗ならアップロード前に失敗。成功デプロイ後（dry-run 以外）にだけ基準を更新 |

テンプレ本体に本番ホスト Secrets は無い。コードと `npm run test:prod-deploy-drift` までがテンプレの検証範囲。案件で Environment／`PROD_FTP_*` を入れたあと、初回は `bootstrap_baseline` で基準を置く。

## GitHub で人が確認すること（案件側）

1. Environments の `production`
2. Deployment branches が `main` だけ
3. Environment secrets に `PROD_FTP_*` がある（値は見えない）。リポジトリの `FTP_*` と別であること
