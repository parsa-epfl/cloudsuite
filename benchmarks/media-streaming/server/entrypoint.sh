#!/bin/bash
cd /etc/nginx
openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout media_streaming_CS_4.key -out media_streaming_CS_4.crt -subj '/C=CH/ST=VD/L=Lausanne/CN=www.mediaStreamingCS4.com'

WORKER_CONNECTIONS="${1:-2000}"
sed -i "s/worker_connections \([0-9]*\);/worker_connections ${WORKER_CONNECTIONS};/g" /etc/nginx/nginx.conf
nginx -g 'daemon off;'
