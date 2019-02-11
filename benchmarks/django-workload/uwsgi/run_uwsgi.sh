#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service

wait_port() {
  while ! netcat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}

. ./uwsgi.cfg

# Start uwsgi container
echo "Starting uwsgi container"
sudo docker run -tid --name uwsgi_container --network host				\
           -e GRAPHITE_ENDPOINT=$GRAPHITE_ENDPOINT                   		\
           -e CASSANDRA_ENDPOINT=$CASSANDRA_ENDPOINT                         	\
           -e MEMCACHED_ENDPOINT="$MEMCACHED_ENDPOINT"                          \
           -e SIEGE_ENDPOINT=$SIEGE_ENDPOINT uwsgi-webtier

wait_port localhost 8000 uwsgi

echo "uWSGI is up and running!"
