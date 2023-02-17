#!/bin/bash

# This script helps to fill the server database with given data set size. 
# Usage: warmup.sh <server_ip> <record_count> <threads=1>
# Each record takes 1KB to store, so if you want a 10GB database, just giving 10M records.

if [ $# -le 1 ]; then
    echo "usage: warm.sh <server_ip> <record_count> <threads=1>"
    exit 0
fi

if [ -z $3 ]; then
	THREADS=1
else
	THREADS=$3
fi

echo '======================================================'
echo "server IP: $1"
echo "Fill the database with $2 records"
echo "Load generator threads count: $THREADS"
echo '======================================================'



echo '======================================================'
echo 'Creating a usertable for the seed server'
echo '======================================================'

first_server=$(cut -d',' -f1 <<< "$1")

exit=0
while [ $exit -eq 0 ]; do
    set +e
    cqlsh -f /setup_tables.txt $first_server
    if [[ "$?" -eq 0 ]]; then
        exit=1
    else
        echo 'Cannot connect to the seed server. Trying again...'
    fi
    set -e
    sleep 5
done

echo '======================================================'
echo 'Populate the database'
echo '======================================================'

/ycsb/bin/ycsb.sh load cassandra-cql -p hosts=$1 -P /ycsb/workloads/workloada -p recordcount=$2 -s -threads $THREADS

echo '======================================================'
echo 'Warm up is done.'
echo '======================================================'


