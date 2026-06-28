# t2025-12-24vite-wp（WordPressテーマ + Vite）

**WordPress サイト制作用テンプレート**（略称 **WP テンプレ**）。GitHub テンプレート名は `t2025-12-24vite-wp`。クラシックテーマとしての制作環境。  
3 リポジトリ間の呼称の正本はナレッジベースの [wiki/operated-repositories.md](/Users/yoshiaki/working/2026-04-23kn/wiki/operated-repositories.md#表記ルール3-リポジトリと型録) を参照。

## この README の責務

- 入口（クイックスタート・注意事項・クローン手順）
- **詳細はすべて [ai-docs/architecture.md](ai-docs/architecture.md)**（導入・設計・開発・デプロイ・トラブル・案件メモ・移行ログ・コーディング規約を集約）
- **文書化・ADR の基準**（汎用）: ナレッジ [template-repository-docs.md](/Users/yoshiaki/working/2026-04-23kn/wiki/template-repository-docs.md)

## 特徴

- **開発**: WordPress で表示しつつ、CSS/JS は Vite dev server（HMR）を参照
- **本番**: `npm run build` で `dist/` を生成し、WordPress 側で読み込み
- **自動デプロイ**: GitHub Actions で FTP 経由の自動デプロイに対応

## クイックスタート

```bash
npm install
npm run dev
# 本番ビルド
npm run build
```

**手順の詳細・新規案件セットアップ・デプロイ**は [ai-docs/architecture.md](ai-docs/architecture.md) の目次から辿る。

**Vite は既定で `localhost:5173`。** 他プロジェクトが同じポートを使うと、dev 判定やアセット読み込みがずれることがある。必要なら **5173 を使っているプロセスを確認したうえで** 終了する（macOS の例）。

```bash
lsof -nP -iTCP:5173 -sTCP:LISTEN
kill $(lsof -t -i :5173)
```

`kill $(lsof -t -i :5173)` は **ポート 5173 を掴んでいるプロセスすべて**にシグナルを送る。コマンド名や PID が Vite / Node の該当サーバーであることを、`lsof` の出力で確かめてから実行すること。

### 導入時の注意（Git / husky）

この制作環境は **Git の利用を前提**としている。Git が無いと `npm install` が `prepare`（husky）で失敗し、**`node_modules` が不完全**になって `vite: command not found` のように見えることがある。**詳細と回避手順**はナレッジ [template-repository-docs.md](/Users/yoshiaki/working/2026-04-23kn/wiki/template-repository-docs.md)（Git・npm・husky）。

**概要（Git を使わない場合）**: `package.json` から `"prepare": "husky"` と `husky` の devDependency を削除してから `npm install` をやり直す。

## リポジトリのクローン（テンプレートから）

```bash
gh repo create 新規リポジトリ名 \
  --template rea1i2e/t2025-12-24vite-wp \
  --private \
  --description "リポジトリの説明文" && \
sleep 5 && \
gh repo clone GitHubのユーザー名/新規リポジトリ名
```

## その他のドキュメント

| ファイル | 役割 |
|----------|------|
| [ai-docs/INDEX.md](ai-docs/INDEX.md) | 技術ドキュメントの索引 |
| [ai-docs/architecture.md](ai-docs/architecture.md) | 技術正本（上記のすべて・A11y のテーマ側手がかりは冒頭） |
| （任意）`ai-docs/decisions/NNNN-topic.md` | **新規 ADR** をテーマ内に残すときの置き場。**過去分・判断基準**はナレッジ [wp-template-decision-records.md](/Users/yoshiaki/working/2026-04-23kn/wiki/wp-template-decision-records.md)・[adr-workflow.md](/Users/yoshiaki/working/2026-04-23kn/wiki/adr-workflow.md) |
| [AGENTS.md](AGENTS.md) | AI エージェント向け |

**アクセシビリティ仮基準の正本**はナレッジ [`wiki/a11y-baseline.md`](/Users/yoshiaki/working/2026-04-23kn/wiki/a11y-baseline.md)。テーマ固有の実装の手がかりは [ai-docs/architecture.md](ai-docs/architecture.md) 冒頭とコーディング規約節、および [AGENTS.md](AGENTS.md) の型録参照。

## ライセンス

プロジェクトに合わせて追記してください。
