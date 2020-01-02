FROM keinos/php8-jit:latest

VOLUME [ "/data", "/app" ]

USER root

COPY src/ /app/

RUN apk --no-cache add openssl \
    && (crontab -l ; echo "0 * * * * /app/toot-clock.php > /dev/null 2>&1 &") | crontab -

WORKDIR /app
ENTRYPOINT [ "/app/run-server.sh" ]
