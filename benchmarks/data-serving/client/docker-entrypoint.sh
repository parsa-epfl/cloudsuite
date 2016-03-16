#!/bin/bash

/ycsb/bin/ycsb load cassandra-10 -p hosts=cassandra-server -P /ycsb/workloads/workloada > /dev/null

/ycsb/bin/ycsb run cassandra-10 -p hosts=cassandra-server -P /ycsb/workloads/workloada
