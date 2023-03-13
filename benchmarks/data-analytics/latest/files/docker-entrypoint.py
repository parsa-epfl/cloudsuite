#!/usr/bin/env python3

import os
import sys
import subprocess
import argparse

args = sys.argv[1:]
parser = argparse.ArgumentParser()
parser.add_argument("--yarn-cores", help="YARN: number of cores for yarn", default=8)
parser.add_argument("--mapreduce-mem", help="MAP_REDUCE: memory per mapreduce worker", default=2096)

args_parsed, unknown = parser.parse_known_args()

yarn_max_mem = int(args_parsed.mapreduce_mem) * int(args_parsed.yarn_cores)
args.append("--yarn-mem=" + str(yarn_max_mem + 812))

print(str(args))
subprocess.call(['./hadoop-start.py'] + args)
