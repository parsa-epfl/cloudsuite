#!/bin/bash

BUILD_DIR=~/cloudsuite/benchmarks/data-analytics/3.0.0/

DIR=$(cd $(dirname $0) && pwd)
. $DIR/config

for node in $nodes; do
  ssh $user@$node "docker build --rm -t cloudsuite/data-analytics:latest $BUILD_DIR"
done

