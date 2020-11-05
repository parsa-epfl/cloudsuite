#!/bin/bash

# This function will wait until the specified port on the specified machine is available
# Parameters: $1 = the IP; $2 = the port; $3 = the name of the service
wait_port() {
  while ! netcat -w 5 "$1" "$2"; do
    echo "Waiting for $3 ..."
    sleep 3
  done
}

wait_port $1 $2 $3
