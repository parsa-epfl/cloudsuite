#!/bin/bash
set -e

# $1: the IP of the server
# $2: the number of httperf clients
# $3: the total number of sessions
# $4: the rate (sessions per seconds)

if [ "$1" = "bash" ]; then
  exec $@
else
  cd /root/run && exec ./benchmark.sh $1 $2 $3 $4
fi
