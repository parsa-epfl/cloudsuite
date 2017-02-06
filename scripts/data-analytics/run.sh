#!/bin/bash

DIR=$(cd $(dirname $0) && pwd)
. $DIR/config

master_node=$(echo $nodes | cut -d " " -f 1)
ssh $user@$master_node "docker exec master benchmark 2>&1"

