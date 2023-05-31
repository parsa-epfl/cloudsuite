#!/bin/bash

# According to the code, the table structure is like the following:
# - id: 4B  (primary key)
# - key: 4B
# - c: 120B
# - pad: 60B
# As a result, each row takes 188B. 
# You can increase the dataset size by adding more 

sysbench oltp_read_write \
    --config-file=$(pwd)/database.conf \
    --threads=8 --report-interval=10 \
    prepare --table_size=1000000 --tables=50 


