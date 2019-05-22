#!/bin/sh

name_host=$(hostname)
name_host=${name_host:-$(cat /etc/hostname)}

php -S $name_host:80 -t /app /app/index.php
