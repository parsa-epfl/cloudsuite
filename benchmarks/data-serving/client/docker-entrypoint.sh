#!/bin/bash

echo server\'s IP is $1
echo ==================

if [ ! -z "$RECORDCOUNT" ]; then
    RECORDCOUNT="-p recordcount=$RECORDCOUNT"
fi

if [ ! -z "$OPERATIONCOUNT" ]; then
    OPERATIONCOUNT="-p operationcount=$OPERATIONCOUNT"
fi

/ycsb/bin/ycsb load cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada $RECORDCOUNT > /dev/null

/ycsb/bin/ycsb run cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada $OPERATIONCOUNT
