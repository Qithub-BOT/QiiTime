#!/bin/sh

# This script is a test for automated build of Docker Cloud.
#
# Mount this file and replace the entrypoint script to run
# from run-server.sh to run-test.sh

name_host=$(hostname)
name_host=${name_host:-$(cat /etc/hostname)}

echo "Host name: ${name_host}" && \
env && \
apk update && \
apk add curl && \
/app/run-server.sh && \
/app/toot-clock.php && \
curl http://$name_host/
