#!/bin/bash

# This script will configure and start the memcached service inside a docker
# container

MEMORY=$2
MIN_BYTES=$3
THREADS=$1
if [ -f /etc/memcached.conf ]; then
    mv /etc/memcached.conf /etc/memcached.conf.old
    echo -e "\n\nBackup /etc/memcached.conf to /etc/memcached.conf.old"
fi

. /scripts/memcached.cfg
#CMD ["-t", "2", "-m", "5020", "-n", "550","-l","0.0.0.0"]
echo -e "\n\nWrite memcached config file ..."
cat > /etc/memcached.conf <<- EOF
	# Daemon mode
	-d
	logfile /var/log/memcached.log
	-m "$MEMORY"
	-p "$PORT"
	-u "$USER"
	-l "$LISTEN"
	-t "$THREADS"
	-n "$MIN_BYTES"
EOF

service memcached start  \
    && tail -f /dev/null
