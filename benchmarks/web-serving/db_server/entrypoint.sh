#!/bin/bash

if [ $# -lt 1 ]; then
  echo "Web server IP is a mandatory parameter."
  exit 1
fi

wget --no-check-certificate -O /elgg_bigDB.dump https://cloudsuite.ch/download/web-serving/elgg_bigDB.dump

WEB_SERVER_IP=$1

# workaround for overlayfs:
# https://docs.docker.com/engine/userguide/storagedriver/overlayfs-driver/#limitations-on-overlayfs-compatibility
find /var/lib/mysql -type f -exec touch {} \;

# Update the hostname/IP to that of the webserver
#sed -i -e"s/HOST_IP/${WEB_SERVER_IP}:8080/" /elgg_bigDB.dump
set root_password=root

MY_SQL=$(find /etc/init.d -name "*mariadb*")
if [ $MY_SQL ]; then
	MY_SQL="mariadb"
else
	MY_SQL="mysql"
fi

service $MY_SQL restart

# Wait for mysql to come up
while :; do $MY_SQL -uroot -p${root_password} -e "status" && break; sleep 1; done
$MY_SQL -uroot -p$root_password -e "create database ELGG_DB;"

# Need bash -c for redirection
bash -c "$MY_SQL -uroot -p$root_password ELGG_DB < /elgg_bigDB.dump"
$MY_SQL -uroot -p$root_password -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$root_password' WITH GRANT OPTION; FLUSH PRIVILEGES;"
service $MY_SQL stop

rm /elgg_bigDB.dump

/usr/sbin/${MY_SQL}d
