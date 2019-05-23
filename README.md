[![](https://Qithub-BOT.github.io/QiiTime/images/LOGO_QiiTime.png)](https://github.com/Qithub-BOT/QiiTime "View QiiTime on GitHub")

# QiiTime

Qiita のマストドン・サーバー [Qiitadon](https://qiitadon.com/) の時報トゥート BOT「QiiTime」の Dockerfile です。

アクセストークンを指定して起動すると１時間ごとに時報をトゥートします。

- Docker イメージ名: `pull qithubbot/qiitime`
- リポジトリ
  - Docker イメージ: https://hub.docker.com/r/qithubbot/qiitime @ Docker Hub
  - GitHub ソース: https://github.com/Qithub-BOT/QiiTime @ GitHub
- Fork した場合、Qiitadon 以外のインスタンスで使いたい場合、トゥート内容などをカスタムしたい場合やログの確認などの詳細な使い方は以下をご覧ください。
  - USAGE_DETAIL.md

## Usage

### `docker-compose` で起動

- ディレクトリ構成
  ```shellsession
  $ chdir /path/to/your/repos/QiiTime/
  $ tree
  .
  └── docker-compose.yml
  ```

- `docker-compose.yml` の内容
  ```yaml
  version: "3"

  services:
    qiitime:
      container_name: qiitime
      image: qithubbot/qiitime:latest
      environment:
        - MSTDN_ACCESSTOKEN='<YOUR Qiitadon ACCESS TOKEN>'
        - IS_MODE_DEBUG='false'
      restart: always

  ```

- 起動
  ```shellsession
  $ ls
  docker-compose.yml
  $ docker-compose up -d
  ...
  ```

- 終了
  ```shellsession
  $ docker-compose down
  ...
  ```

### `docker run` で起動

- 起動
  ```bash
  docker run --rm \
    --detach \
    --name qiitime \
    -e MSTDN_ACCESSTOKEN '<YOUR Qiitadon ACCESS TOKEN>' \
    -e IS_MODE_DEBUG 'false' \
    qithubbot/qiitime
  ```

- 終了
  ```bash
  docker kill qiitime
  ```

