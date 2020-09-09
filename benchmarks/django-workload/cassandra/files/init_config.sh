#!/bin/bash

# This script is called when the cassandra image is built
# It receives the hostname as a parameter (cassandra)

HOSTNAME=$1

# Settings for 2-socket Broadwell-EP with 22 cores per socket,
# all services running on same machine

sed -e "s/listen_address: localhost/listen_address: $HOSTNAME/g"                               \
    -e "s/# broadcast_address: 1.2.3.4/broadcast_address: $HOSTNAME/g"                         \
    -e "s/# broadcast_rpc_address: 1.2.3.4/broadcast_rpc_address: $HOSTNAME/g"                 \
    -e "s/seeds: \"127.0.0.1\"/seeds: \"$HOSTNAME\"/g"                                         \
    -e "s/rpc_address: localhost/rpc_address: $HOSTNAME/g"                                     \
    -e "s/# listen_on_broadcast_address: false/listen_on_broadcast_address: true/g"            \
    -e "s/start_rpc: false/start_rpc: true/g"                                                  \
    -e "s/concurrent_reads: 32/concurrent_reads: 64/g"                                         \
    -e "s/concurrent_writes: 32/concurrent_writes: 128/g"                                      \
    -e "s/concurrent_counter_writes: 32/concurrent_counter_writes: 128/g"                      \
    -e "s/concurrent_materialized_view_writes: 32/# concurrent_materialized_view_writes: 32/g" \
    -i /etc/cassandra/cassandra.yaml
