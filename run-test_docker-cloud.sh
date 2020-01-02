#!/bin/sh
# This script is a test for the automated build of Docker Cloud.
#
# Mount this file to /app/run-test.sh as "entrypoint".

echo '- Running test ...'

name_host=$(hostname)
name_host=${name_host:-$(cat /etc/hostname)}

echo "- Host name: ${name_host}"
echo '- Installing curl ...'
apk --no-cache add curl && \
echo 'Installed at:' $(which curl) && \
echo '- Running server ...'
nohup /app/run-server.sh &

echo '- Tooting ...'
/app/toot-clock.php && \
echo '- cURL server'
curl -s http://$name_host/
