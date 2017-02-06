#!/bin/bash

DIR=$(cd $(dirname $0) && pwd)
. $DIR/config

for node in $nodes; do
  ssh $user@$node 'docker ps -q --filter ancestor=cloudsuite/data-analytics | xargs -r docker stop | xargs -r docker rm'
  ssh $user@$node 'docker ps -q --filter ancestor=cloudsuite/hadoop | xargs -r docker stop | xargs -r docker rm'
done

