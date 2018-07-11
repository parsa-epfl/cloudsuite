#!/bin/bash

echo server\'s IP is $1
echo ==================

if [ ! -z "$RECORDCOUNT" ]; then
    RECORDCOUNT="-p recordcount=$RECORDCOUNT"
fi

if [ ! -z "$OPERATIONCOUNT" ]; then
    OPERATIONCOUNT="-p operationcount=$OPERATIONCOUNT"
fi

while true; do
    sleep 5
    out=`/ycsb/bin/ycsb load cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada $RECORDCOUNT`;
    if ! [[ $out =~ "NoHostAvailableException" ]] && ! [[ $out =~ "Keyspace 'ycsb' does not exist" ]]; then break; fi
    echo Cassandra is not up yet. Retrying...
done 

/ycsb/bin/ycsb run cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada $OPERATIONCOUNT
