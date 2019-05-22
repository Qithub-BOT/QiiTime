# QiiTime

1. `mastodon.env.sample` を `mastodon.env` にリネーム
2. `mastodon.env` 内の `MSTDN_ACCESSTOKEN` にアクセストークンを設定
3. `docker-compose up -d`

手動トゥート

- `docker container ls`
- `docker exec -it qiitime /bin/sh`
- `./toot-clock.php`
