#!/bin/bash

echo server\'s IP is $1
echo ==================

/ycsb/bin/ycsb load cassandra-10 -p hosts=$1 -P /ycsb/workloads/workloada > /dev/null

/ycsb/bin/ycsb run cassandra-10 -p hosts=$1 -P /ycsb/workloads/workloada
