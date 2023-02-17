#!/bin/bash

# Usage: load.sh <server_ip> <record_count> <target_load> <threads=1> <operation_count=load * 60>

if [ $# -le 2 ]; then
	echo "usage: load.sh <server_ip> <record_count> <target_load> <threads=1> <operation_count=load * 60>"
	exit 0
fi

if [ -z $4 ]; then
	THREADS=1
else
	THREADS=$4
fi

if [ -z $5 ]; then
	let OP_COUNT="60*$3"
else
	OP_COUNT=$5
fi


echo '======================================================'
echo "server IP: $1"
echo "Database record count: $2"
echo "Target load: $3 rps"
echo "Loader threads count: $THREADS"
echo "Opeartion count: $OP_COUNT"
echo "Make sure you have run the warmup.sh before loading the server, and use the same record count here."
echo '======================================================'

/ycsb/bin/ycsb.sh run cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada \
        -p recordcount=$2 -p operationcount=$OP_COUNT \
        -threads $THREADS -target $3 -s


