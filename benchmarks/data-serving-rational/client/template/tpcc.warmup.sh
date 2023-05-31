#!/bin/bash

sysbench tpcc \
    --config-file=$(pwd)/database.conf \
    --threads=8 \
    prepare --scale=10