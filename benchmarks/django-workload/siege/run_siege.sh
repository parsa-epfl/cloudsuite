#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service

wait_port() {
  while ! netcat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}


. ./siege.cfg
# Start siege container
echo "Starting siege container"
docker run -ti --name siege_container --volume=/tmp:/tmp --privileged 		\
           --network host -e ATTEMPTS=10                             		\
           -e TARGET_ENDPOINT=$UWSGI_ENDPOINT -e SIEGE_WORKERS=$SIEGE_WORKERS 	\
           siege-webtier

