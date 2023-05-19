#!/bin/bash

wget --progress=bar:force -O - --no-check-certificate http://datasets.epfl.ch/cloudsuite/ELGG_DB.tar.gz | tar -zxvf -


# workaround for overlayfs:
# https://docs.docker.com/engine/userguide/storagedriver/overlayfs-driver/#limitations-on-overlayfs-compatibility
find /var/lib/mysql -type f -exec touch {} \;

set root_password=root

MY_SQL=$(find /etc/init.d -name "*mariadb*")
if [ $MY_SQL ]; then
	MY_SQL="mariadb"
else
	MY_SQL="mysql"
fi

service $MY_SQL stop

rm -rf /var/lib/mysql/*

mariabackup --prepare --target-dir=/backup/
mariabackup --move-back --target-dir=/backup/

chown -R mysql:mysql /var/lib/mysql/

service $MY_SQL start
bash
