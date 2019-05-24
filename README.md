<img src="https://Qithub-BOT.github.io/QiiTime/images/LOGO_QiiTime.png" width="100%" />

[![](https://images.microbadger.com/badges/image/qithubbot/qiitime.svg)](https://microbadger.com/images/qithubbot/qiitime "View image info on microbadger.com") [![](https://img.shields.io/docker/cloud/automated/qithubbot/qiitime.svg)](https://hub.docker.com/r/qithubbot/qiitime "View on Docker Hub") [![](https://img.shields.io/docker/cloud/build/qithubbot/qiitime.svg)](https://hub.docker.com/r/qithubbot/qiitime/builds "View Builds on Docker Hub")

# QiiTime

Qiita のマストドン・サーバー [Qiitadon](https://qiitadon.com/) の時報トゥート BOT「QiiTime」の Dockerfile です。

アクセストークンを指定して起動すると１時間ごとに時報をトゥートします。

- Docker イメージ名: `pull qithubbot/qiitime`
- リポジトリ
  - イメージ: https://hub.docker.com/r/qithubbot/qiitime @ Docker Hub
  - ソース: https://github.com/Qithub-BOT/QiiTime @ GitHub
- 詳細な使い方は以下をご覧ください。
  - [USAGE_DETAIL.md](https://github.com/Qithub-BOT/QiiTime/blob/master/USAGE_DETAIL.md)<br>（Qiitadon 以外のインスタンスで使いたい場合、トゥート内容などをカスタムしたい場合やログの確認方法など）

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
        - MSTDN_ACCESSTOKEN='[YOUR Qiitadon ACCESS TOKEN Here]'
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
    -e MSTDN_ACCESSTOKEN '[OUR Qiitadon ACCESS TOKEN Here]' \
    -e IS_MODE_DEBUG 'false' \
    qithubbot/qiitime
  ```

- 終了
  ```bash
  docker kill qiitime
  ```
