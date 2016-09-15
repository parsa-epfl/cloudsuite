#!/bin/bash


DB_SERVER_IP=${1:-"mysql_server"}
MEMCACHE_SERVER_IP=${2:-"memcache_server"}
sed -i -e"s/mysql_server/${DB_SERVER_IP}/" elgg/engine/settings.php
sed -i -e"s/memcache_server/${MEMCACHE_SERVER_IP}/" elgg/engine/settings.php


FPM_CHILDREN=${3:-80}
sed -i -e"s/pm.max_children = 5/pm.max_children = ${FPM_CHILDREN}/" /etc/php5/fpm/pool.d/www.conf

service php5-fpm restart
service nginx restart
bash
