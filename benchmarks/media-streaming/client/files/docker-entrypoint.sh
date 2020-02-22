#!/bin/bash
set -e

if [ "$1" = "bash" ]; then
  exec $@
else
  if [ "$2" = "True" ]
  then
    mkdir -p /videos/logs
    cp /root/logs/cl* /videos/logs/
  fi
  cd /root/run && exec ./benchmark.sh $1
fi