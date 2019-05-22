FROM keinos/php7-alpine:latest

VOLUME [ "/data", "/app" ]

RUN apk update \
    && apk add --update \
      php-mbstring@php \
      php-json@php \
      php-ctype@php \
      php-openssl@php \
    && rm -rf /var/cache/apk/*

COPY src/ /app/

WORKDIR /app

ENTRYPOINT [ "/app/run-server.sh" ]
