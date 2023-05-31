#!/bin/bash

sysbench tpcc \
    --config-file=$(pwd)/database.conf \
    --threads=8 --time=360 --report-interval=10 \
    run --scale=10