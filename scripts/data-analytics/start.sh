#!/bin/bash

if [ $# -lt 1 ]; then
  echo "Usage: $0 nslaves [host]"
  exit 1
fi

DIR=$(cd $(dirname $0) && pwd)
. $DIR/config

nodes=($nodes)
nodes_index=0
node=${nodes[0]}
master_node=${nodes[0]}

function next_node() {
  nodes_index=$(( ($nodes_index + 1) % ${#nodes[@]} ))
  node=${nodes[$nodes_index]}
}

case $2 in
  host)
    ssh $user@$master_node 'docker run -d --net host --name master cloudsuite/data-analytics master'
    for (( i=1; i<=$1; i+=1 )); do
      ssh $user@$node "docker run -d --net host --name slave$i cloudsuite/hadoop slave $master_node"
      next_node
    done
    ;;
  *)
    ssh $user@$master_node 'docker run -d --net hadoop-net --name master --hostname master cloudsuite/data-analytics master'
    for (( i=1; i<=$1; i+=1 )); do
      ssh $user@$node "docker run -d --net hadoop-net --name slave$i --hostname slave$i cloudsuite/hadoop slave"
      next_node
    done
    ;;
esac

