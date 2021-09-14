#!/bin/bash

if [ $# -lt 1 ]; then
  echo "Web server IP is a mandatory parameter."
  exit 1
fi

WEB_SERVER_IP=$1

# workaround for overlayfs:
# https://docs.docker.com/engine/userguide/storagedriver/overlayfs-driver/#limitations-on-overlayfs-compatibility
find /var/lib/mysql -type f -exec touch {} \;

# Update the hostname/IP to that of the webserver
sed -i -e"s/HOST_IP/${WEB_SERVER_IP}:8080/" /elgg_db.dump
set root_password=root
service mysql restart

# Wait for mysql to come up
while :; do mysql -uroot -p${root_password} -e "status" && break; sleep 1; done
mysql -uroot -p$root_password -e "create database ELGG_DB;"

# Need bash -c for redirection
bash -c "mysql -uroot -p$root_password ELGG_DB < /elgg_db_dump.txt"
mysql -uroot -p$root_password -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$root_password' WITH GRANT OPTION; FLUSH PRIVILEGES;"
service mysql stop

/usr/sbin/mysqld
