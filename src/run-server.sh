#!/bin/sh

name_host=$(hostname)
name_host=${name_host:-$(cat /etc/hostname)}
option_cron=''

if [ "$IS_MODE_DEBUG" = 'true' ]; then
    # Run cron daemon and PHP built-in server
    crond -L /data/log_cron.txt -l 0 && \
    php -S $name_host:80 -t /app /app/index.php
else
    rm -rf /data/*
    # Run cron daemon and PHP built-in server
    crond -L /data/log_cron.txt -l 8 && \
    php -S $name_host:80 -t /app /app/index.php
fi
