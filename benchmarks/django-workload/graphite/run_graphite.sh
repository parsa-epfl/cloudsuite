#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service
wait_port() {
  while ! ncat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}

# Start graphite container
echo "Starting graphite container"
sudo docker run -tid --name graphite_container --network host graphite-webtier

wait_port localhost 80 graphite
echo "Graphite is up and running!"
