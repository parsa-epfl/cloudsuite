#!/bin/bash
set -x
set root_password=$1

service mysql restart

# Wait for mysql to come up
while :; do mysql -uroot -p${root_password} -e "status" && break; sleep 1; done

mysql -uroot -p$root_password -e "create database ELGG_DB;"

# Need bash -c for redirection
bash -c "mysql -uroot -p$root_password ELGG_DB < /elgg_db.dump"

mysql -uroot -p$root_password -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$root_password' WITH GRANT OPTION; FLUSH PRIVILEGES;"

service mysql stop 

/usr/sbin/mysqld
