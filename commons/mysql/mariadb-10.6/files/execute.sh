#!/bin/bash
set -x
set root_password=$1

MY_SQL=$(find /etc/init.d -name "*mariadb*")
if [ $MY_SQL ]; then
	MY_SQL="mariadb"
else
	MY_SQL="mysql"
fi

service $MY_SQL restart

# Wait for mysql to come up
while :; do mysql -uroot -p${root_password} -e "status" && break; sleep 1; done

# # Need bash -c for redirection

mysql -uroot -p$root_password -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY '$root_password' WITH GRANT OPTION; FLUSH PRIVILEGES;"

service $MY_SQL stop 

/usr/sbin/mysqld
