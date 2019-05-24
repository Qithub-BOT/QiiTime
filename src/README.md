# ディレクトリ構成とファイルの説明

```shellsession
$ tree ./src
./src
├── README.md             // このファイル
├── index.php             // フロントエンド（PHP ビルトイン Web サーバーから呼び出されます）
│                         // 最後にトゥートした内容の JSON を返します。（/data/toot.json の内容）
├── list-replace.inc.php  // トゥートのテンプレート置き換え可能な文字の設定
├── run-server.sh         // コンテナ起動時に Web および cron のデーモンを起動するスクリプト
├── toot-clock.php        // 時報をトゥートする PHP  スクリプト本体
├── toot-main.tpl         // トゥートの本文のテンプレート（デフォルトの内容）
└── toot-spoiler.tpl      // トゥートの警告文のテンプレート（てフォルトの内容）
```
