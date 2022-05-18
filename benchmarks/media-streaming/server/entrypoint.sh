#!/bin/bash

WORKER_CONNECTIONS="${1:-2000}"
sed -i "s/worker_connections \([0-9]*\);/worker_connections ${WORKER_CONNECTIONS};/g" /etc/nginx/nginx.conf
nginx -g 'daemon off;'

