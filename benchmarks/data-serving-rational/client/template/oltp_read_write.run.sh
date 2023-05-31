#!/bin/bash

sysbench oltp_read_write \
    --config-file=$(pwd)/database.conf \
    --threads=8 --report-interval=10 \
    run --table_size=1000000 --tables=50 
