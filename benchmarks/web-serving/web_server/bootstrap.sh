#!/bin/bash
set -e
set -x

DB_SERVER_IP=${1:-"mysql_server"}
DB_SERVER_UNAME=${2:-"root"}
DB_SERVER_PASS=${3:-"root"}
MEMCACHE_SERVER_IP=${4:-"memcache_server"}
sed -i -e"s/mysql_server/${DB_SERVER_IP}/" elgg/engine/settings.php
sed -i -e"s/'memcache_server'/'${MEMCACHE_SERVER_IP}'/" elgg/engine/settings.php

sed -i -e"s/HOST_IP/${DB_SERVER_IP}:8080/" /elgg_db.dump

if [[ ! -z "`mysql -h${DB_SERVER_IP} -u${DB_SERVER_UNAME} -p${DB_SERVER_PASS} -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='ELGG_DB';" 2>/dev/null`" ]];
then
  echo "ELGG_DB DATABASE ALREADY EXISTS"
else
  echo "CREATING ELGG_DB DATABASE"
  mysql -h${DB_SERVER_IP} -u${DB_SERVER_UNAME} -p${DB_SERVER_PASS} -e "create database ELGG_DB;"
  bash -c "mysql -h${DB_SERVER_IP} -u${DB_SERVER_UNAME} -p${DB_SERVER_PASS} ELGG_DB < /elgg_db.dump"
fi

if [[ ! -z "${HHVM}" && "${HHVM}" = "true" ]]; then
	chmod 700 /tmp/configure_hhvm.sh
	/tmp/configure_hhvm.sh
else
	cat /tmp/nginx_sites_avail.append >> /etc/nginx/sites-available/default
	FPM_CHILDREN=${5:-80}
	sed -i -e"s/pm.max_children = 5/pm.max_children = ${FPM_CHILDREN}/" /etc/php5/fpm/pool.d/www.conf

	service php5-fpm restart
fi

service nginx restart
bash
