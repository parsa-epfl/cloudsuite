#!/usr/bin/env python3

import os
import sys
import subprocess
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("--run", help="Run the benchmark, must be warmuped up before with --warmup", action='store_true')
parser.add_argument("--warmup", help="Warmup the benchmark, then can be ran with --run", action='store_true')
parser.add_argument("--threads", "-t", help="Number of threads for the client", default=8, type=int)
parser.add_argument("--report-interval", "-ri", help="Report interval for metrics in seconds", default=10, type=int)
parser.add_argument("--time", "-s", help="Length of the benchmark in seconds", default=360, type=int)
parser.add_argument("--scale", "-n", help="Scale of the dataset", default=10, type=int)
parser.add_argument("--rate", "-r", help="The expected load (transaction / sec)", type=int)

args_parsed, unknown = parser.parse_known_args()

# Warmup
if not args_parsed.warmup and not args_parsed.run:
    print("Need to pass at least --run or --warmup argument")
    exit()



if args_parsed.warmup:
	os.system(f"sysbench tpcc --config-file=/root/template/database.conf --threads={args_parsed.threads} prepare --scale={args_parsed.scale}")
elif not args_parsed.rate:
	os.system(f"sysbench tpcc --config-file=/root/template/database.conf --threads={args_parsed.threads} --time={args_parsed.time} --report-interval={args_parsed.report_interval} run --scale={args_parsed.scale}")
else:
	os.system(f"sysbench tpcc --config-file=/root/template/database.conf --threads={args_parsed.threads} --time={args_parsed.time} --report-interval={args_parsed.report_interval} run --scale={args_parsed.scale} --rate={args_parsed.rate}")
