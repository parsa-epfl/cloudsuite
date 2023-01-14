#!/bin/bash

# Usage: load.sh <server_ip> <record_count> <target_load> <operation_count> <threads>

echo '======================================================'
echo "server IP: $1"
echo "Database record count: $2"
echo "Target load: $3 rps"
echo "Opeartion count: $4"
echo "Loader thread: $5"
echo "Make sure you have run the warmup.sh before loading the server, and use the same record count here."
echo '======================================================'

/ycsb/bin/ycsb run cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada \
        -p recordcount=$2 -p operationcount=$4 \
        -threads $5 -target $3


