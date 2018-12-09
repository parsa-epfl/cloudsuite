#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service

wait_port() {
  while ! netcat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}


# Start memcached container
echo "Staring memcached container"
docker run -tid --name memcached_container --network host memcached-webtier

wait_port localhost 11211 memcached
echo "Memcached is up and running!"

