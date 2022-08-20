#!/bin/bash
ROOT_SERVER=${1:-"root_server"} # it should be given like: "http:\/\/IP:8080\/" or "https:\/\/IP:8443:\/"
DB_SERVER_IP=${2:-"mysql_server"}
MEMCACHE_SERVER_IP=${3:-"memcache_server"}

sed -i -e"s/root_server/${ROOT_SERVER}/" elgg/elgg-config/settings.php
sed -i -e"s/mysql_server/${DB_SERVER_IP}/" elgg/elgg-config/settings.php
sed -i -e"s/'memcache_server'/'${MEMCACHE_SERVER_IP}'/" elgg/elgg-config/settings.php

cat /tmp/nginx_sites_avail.append >> /etc/nginx/sites-available/default

FPM_CHILDREN=${4:-80}
sed -i -e"s/pm.max_children = 5/pm.max_children = ${FPM_CHILDREN}/" /etc/php/${VERSION}/fpm/pool.d/www.conf

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
