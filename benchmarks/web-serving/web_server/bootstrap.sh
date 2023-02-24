#!/bin/bash
PROTOCOL=${1:-"http"} # the protocol to access the server, valid values are 'http' and 'https'
ROOT_SERVER=${2:-"root_server"} 
PORT_NUMBER=8080

if [ $PROTOCOL == 'https' ]; then
    PORT_NUMBER=8443
fi

DB_SERVER_IP=${3:-"mysql_server"}
MEMCACHE_SERVER_IP=${4:-"memcache_server"}

sed -i -e"s/http_protocol/${PROTOCOL}/" elgg/elgg-config/settings.php
sed -i -e"s/root_server/${ROOT_SERVER}/" elgg/elgg-config/settings.php
sed -i -e"s/port_number/${PORT_NUMBER}/" elgg/elgg-config/settings.php
sed -i -e"s/mysql_server/${DB_SERVER_IP}/" elgg/elgg-config/settings.php
sed -i -e"s/'memcache_server'/'${MEMCACHE_SERVER_IP}'/" elgg/elgg-config/settings.php

if [ $PROTOCOL == 'https' ]; then
    cat /tmp/nginx_sites_avail_tls.append >> /etc/nginx/sites-available/default
elif [ $PROTOCOL == 'http' ]; then
    cat /tmp/nginx_sites_avail_pt.append >> /etc/nginx/sites-available/default
fi

FPM_CHILDREN=${5:-4}
WORKER_PROCESS=${6:-"auto"}

sed -i -e"s/pm.max_children = 5/pm.max_children = ${FPM_CHILDREN}/" /etc/php/${VERSION}/fpm/pool.d/www.conf
sed -i -e"s/pm = dynamic/pm = static/" /etc/php/${VERSION}/fpm/pool.d/www.conf


sed -i -e"s/worker_processes auto;/worker_processes ${WORKER_PROCESS};/" /etc/nginx/nginx.conf
sed -i -e's/;opcache.file_cache=/opcache.file_cache=\/tmp/' /etc/php/${VERSION}/fpm/php.ini     

service php${VERSION}-fpm restart

# enabling JIT
# only the function jit-mode works. Tracing mode causes crashes!
ARCH="$(dpkg --print-architecture)"
case "${ARCH}" in
    # only Php 8.1 has JIT support for ARM
    aarch64|arm64)
        if [[ ${VERSION} == 8.1 ]]; then
             echo -e "opcache.enable=1\nopcache.jit_buffer_size=100M\nopcache.jit=function" >> /etc/php/${VERSION}/fpm/php.ini
        fi
    ;;
    amd64|x86_64)
        if [[ ${VERSION} == 8.0 || ${VERSION} == 8.1 ]]; then
            echo -e "opcache.enable=1\nopcache.jit_buffer_size=100M\nopcache.jit=function" >> /etc/php/${VERSION}/fpm/php.ini
        fi
    ;;
    *)
        echo "JIT is not supported on this architecture"
    ;;
esac

cd /etc/nginx

openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout web_serving_CS_4.key -out web_serving_CS_4.crt -subj '/C=CH/ST=VD/L=Lausanne/CN=www.webServingCS4.com'
        
service php${VERSION}-fpm restart

service nginx restart
bash
