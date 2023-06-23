#!/usr/bin/env python3

import os
import sys
import subprocess
import argparse

# According to the code, the table structure is like the following:
# - id: 4B  (primary key)
# - key: 4B
# - c: 120B
# - pad: 60B
# As a result, each row takes 188B. 
# You can increase the dataset size by adding more 

parser = argparse.ArgumentParser()
parser.add_argument("--run", help="Run the benchmark, must be warmuped up before with --warmup", action='store_true')
parser.add_argument("--warmup", help="Warmup the benchmark, then can be ran with --run", action='store_true')
parser.add_argument("--threads", "-t", help="Number of threads for the client", default=8, type=int)
parser.add_argument("--report-interval", "-ri", help="Report interval for metrics in seconds", default=10, type=int)
parser.add_argument("--record-count", "-c", help="Record count per table. Each record is 188B", default=1000000, type=int)
parser.add_argument("--tables", "-n", help="Number of tables with `table_size` rows each", default=50, type=int)
parser.add_argument("--rate", "-r", help="The expected load (transaction / sec)", type=int)
parser.add_argument("--time", "-s", help="Length of the benchmark in seconds", default=360, type=int)



args_parsed, unknown = parser.parse_known_args()

# Warmup
if not args_parsed.warmup and not args_parsed.run:
    print("Need to pass at least --run or --warmup argument")
    exit()

if args_parsed.warmup:
	os.system(f"sysbench oltp_read_write --config-file=/root/template/database.conf --threads={args_parsed.threads} --time={args_parsed.time} --report-interval={args_parsed.report_interval}  prepare --table_size={args_parsed.record_count} --tables={args_parsed.tables}")
elif not args_parsed.rate:
	os.system(f"sysbench oltp_read_write --config-file=/root/template/database.conf --threads={args_parsed.threads} --time={args_parsed.time} --report-interval={args_parsed.report_interval} run --table_size={args_parsed.record_count} --tables={args_parsed.tables}")
else:
    os.system(f"sysbench oltp_read_write --config-file=/root/template/database.conf --threads={args_parsed.threads} --time={args_parsed.time} --report-interval={args_parsed.report_interval} run --table_size={args_parsed.record_count} --tables={args_parsed.tables} --rate={args_parsed.rate}")