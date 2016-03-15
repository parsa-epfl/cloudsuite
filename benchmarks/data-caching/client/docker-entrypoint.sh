#!/bin/bash
set -e
set -x

if [ "$1" = '-rps' ]; then
	# default configuration
	echo "dc-server, 11211" > "/usr/src/memcached/memcached_client/servers.txt"
	/usr/src/memcached/memcached_client/loader \
		-a /usr/src/memcached/twitter_dataset/twitter_dataset_unscaled \
		-o /usr/src/memcached/twitter_dataset/twitter_dataset_30x \
		-s /usr/src/memcached/memcached_client/servers.txt \
		-w 4 -S 30 -D 4096 -j

	/usr/src/memcached/memcached_client//loader \
		-a /usr/src/memcached/twitter_dataset/twitter_dataset_30x \
		-s /usr/src/memcached/memcached_client/servers.txt \
		-g 0.8 -c 200 -w 8 -e -r "$2" -t 123 -T 120

else
	# custom command
	exec "$@"
fi
