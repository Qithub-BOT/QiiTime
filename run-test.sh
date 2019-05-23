#!/bin/sh

# This script is a test for automated build of Docker Cloud.
#
# Mount this file and replace the entrypoint script to run
# from run-server.sh to run-test.sh

echo '- Running test ...'

name_host=$(hostname)
name_host=${name_host:-$(cat /etc/hostname)}

echo "- Host name: ${name_host}"
echo '- Env:'; env
echo '- Installing curl ...'
apk update && \
apk add curl && \
echo 'Installed at:' $(which curl) && \
echo '- Running server ...'
nohup /app/run-server.sh &

echo '- Tooting ...'
/app/toot-clock.php && \
echo '- cURL server'
curl -s http://$name_host/
