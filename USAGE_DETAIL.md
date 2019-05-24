# 使い方の詳細

## アクセス・トークンの取得の仕方

1. 自分の Mastodon インスタンス（マストドン・サーバー）にログインする。
2. 歯車アイコンから設定を開き「開発」を選ぶ。
3. 「新規アプリ」ボタンを押下し、以下の設定で QiiTime 用のアプリ登録を行います。

    - アプリの名前： QiiTime
    - アプリのウェブサイト： 任意（`https://github.com/Qithub-BOT/QiiTime` など）
    - リダイレクト URI: 特に変更なし
    - アクセス権： `write` 以外のチェックを外す
    - 「送信」ボタン押下でアプリ登録する

4. 「アプリ」一覧から作成した「QiiTime」の登録を開く。
5. 「アクセストークン」の欄からアクセス・トークンをコピーする。

## アプリの設定を変更したい（コンテナ内の環境変数について）

時報アプリに必要な各種設定（アクセス・トークンなど）はコンテナ内の環境変数で制御しています。イメージのビルド時やコンテナ起動時に以下の**環境変数を変更することでアプリの挙動を変えることができます**。

- `MSTDN_ACCESSTOKEN`（必須）
  - インスタンスで発行したアクセス・トークンです。デフォルトは空に設定されています。
- `IS_MODE_DEBUG` = `true` `false`（オプション設定）
  - デバッグ用の詳細な出力をします。`false` 以外に設定された場合、ログはコンテナ内の `/data/log.txt` に出力されます。デフォルトは `false`。
- `MSTDN_VISIBILITY` = `public` `unlisted` `direct`（オプション設定）
  - トゥートの公開範囲を指定します。デフォルトは `direct`（ダイレクト）です。
- `MSTDN_SCHEMA` = 任意の文字列（オプション設定）
  - Qiitadon 以外のインスタンスを指定する場合に `https` 以外のスキーマを指定します。デフォルトは `https`。
- `MSTDN_HOST` = 任意の文字列（オプション設定）
  - Qiitadon 以外のインスタンスを指定する場合に `qiitadon.com` 以外のホストを指定します。デフォルトは `qiitadon.com`。
- `MSTDN_TOOT_MAIN` = 任意の文字列（オプション設定）
  - トゥート時の本文に記載する文を指定できます。"`%%DATE_TIME%%`"などの置き換え文字を含めるとテンプレートとして動作します。デフォルトは QiiTime の紹介文です。
  - [テンプレートとして使える置き換え文字一覧](https://github.com/Qithub-BOT/QiiTime/blob/master/src/list-replace.inc.php)
- `MSTDN_TOOT_SPOILER`
  - トゥート時の警告文として表示する文を指定できます。指定されるとトゥートの本文は「もっと見る」として隠し表示になり、逆にこの警告文が表示されるようになります。"`%%DATE_TIME%%`"などの置き換え文字を含めるとテンプレートとして動作します。デフォルトは時報情報です。
  - 警告文を表示させない（本文のみを表示させたい）場合は `no_spoiler` を指定します。
  - [テンプレートとして使える置き換え文字一覧](https://github.com/Qithub-BOT/QiiTime/blob/master/src/list-replace.inc.php)

アクセストークンなど、イメージ作成時に設定した環境変数を埋め込みたくない場合は、以下の Qiita 記事を参照にしてください。

- 「[Docker で環境変数をホスト OS 側からゲスト OS に渡す方法（各種）](https://qiita.com/KEINOS/items/518610bc2fdf5999acf2)」@ Qiita

## Qiitadon 以外で使いたい

コンテナ内の環境変数 `MSTDN_HOST` を変えることで、Qiitadon 以外のインスタンス（マストドン・サーバー）でも使えます。

```shellsession
$ docker run --rm -d \
  -e MSTDN_SCHEMA=https
  -e MSTDN_HOST=[YOUR HOST NAME] \
  -e MSTDN_ACCESSTOKEN=[YOUR HOSTs ACCESS TOKEN] \
  qithubbot/qiitime
```

## トゥートの公開範囲を変更したい

コンテナ内の環境変数 `MSTDN_VISIBILITY` を変えることで、デフォルトの `direct`（ダイレクト）以外のトゥートが指定できます。

```shellsession
$ docker run --rm -d \
  -e MSTDN_VISIBILITY=public
  -e MSTDN_ACCESSTOKEN=[YOUR HOSTs ACCESS TOKEN] \
  qithubbot/qiitime
```

## ローカルでビルドしたイメージを使いたい

Docker Hub にあるビルド済みのイメージを使うのではなく、ローカルで Docker イメージを作成（ビルド）して使いたい場合は、以下の方法があります。

### `docker` でローカル・ビルド

リポジトリをクローンして作成します。

```shellsession
$ # QiiTime のリポジトリをクローンして、ディレクトリ移動
$ git clone https://github.com/Qithub-BOT/QiiTime.git -o QiiTime && cd $_
$ # イメージをビルドする（イメージ名：`my_qiitime`）
$ docker build -n my_qiitime . && docker image prune -f
...
$ # QiiTime をローカル・イメージで起動
$ docker run --rm -d -e MSTDN_ACCESSTOKEN='[YOUR Qiitadon ACCESS TOKEN]' my_qiitime
...
$ # コンテナの常駐確認
$ docker container ls | grep my_qiitime
...
$ # QiiTime コンテナの終了
$ docker kill my_qiitime
...
$ # 不要になったものを削除
$ docker container prune -f && docker image prune -f && docker volume prune -f
...
```

### `docker-compose` でローカル・ビルド

`docker-compose` でローカルにイメージをビルドしたい場合、git リポジトリを `docker-compose.yml` に指定します。

```yaml
version: "3.7"

services:
  qiitime:
    container_name: qiitime
    build:
      context: https://github.com/Qithub-BOT/QiiTime.git
    image: qiitime
    volumes:
      - ./data/:/data/
    env_file: mastodon.env

```

## ローカルでビルドのテスト

ローカルでビルドしたイメージの動作テストは、`clone` したディレクトリ内で行います。アクセス・トークン以外の環境変数の設定は、`docker-compose.test.yml` で行なっています。

```shellsession
$ # Clone 先に移動
$ cd /path/to/local/cloned/QiiTime
$ export MSTDN_ACCESSTOKEN=[YOUR ACCESS TOKEN] \
  && docker-compose -f docker-compose.test.yml up --build
$ # $? が 0 なら成功
$ echo $?
0
```

## 他のコンテナからトゥート内容を取得したい

同一 Docker/Docker-Compose ネットワーク内のコンテナから HTTP で **`http://qiitime/` にアクセスするとトゥート済みの情報を JSON 形式で取得できます**。

```bash
curl http://qiitime/
```
