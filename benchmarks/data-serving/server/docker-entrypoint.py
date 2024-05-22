#!/usr/bin/env python3

import socket
def get_ip():
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.settimeout(0)
    try:
        # doesn't even have to be reachable
        s.connect(('8.8.8.8', 1))
        IP = s.getsockname()[0]
    except Exception:
        IP = '127.0.0.1'
    finally:
        s.close()
    return IP

import argparse

parser = argparse.ArgumentParser()

parser.add_argument("--listen-ip", "-a", help="The listening IP address of Cassandra. If no value provided, the script tries to find the primary IP address by itself.", default=get_ip())
parser.add_argument("--reader-count", "-r", type=int, help="The number of reader threads. Recommended value: 16 per disk to store data. Default is 16", default=16)
parser.add_argument("--writer-count", "-w", type=int, help="The number of writer threads. Recommended value: 8 per CPU core. Default is 32.", default=32)
parser.add_argument("--heap-size", type=int, help="The size of JVM heap in GB. Default is max(min(1/2 ram, 1GB), min(1/4 ram, 8GB)).")
parser.add_argument("--seed-server-ip", help="The IP address of the seed server. This option is only for multiple-node deployment.")
parser.add_argument("--affinity", help="The CPU ids (separated by comma) given to Cassandra to set JVM affinity. By default, Cassandra would use all CPU cores.")
parser.add_argument("--row-cache", help="The size of the row cache. Also specify the unit, example 16GiB or 256MiB. By default, the row cache is disabled.", default="0MiB")


args = parser.parse_args()

import yaml
import os
import os.path as path
import shutil

CASSANDRA_CONFIG = os.environ["CASSANDRA_CONFIG"]

# Backup the original file
if not path.exists(f"{CASSANDRA_CONFIG}/cassandra.yaml.bak"):
    shutil.copy(f"{CASSANDRA_CONFIG}/cassandra.yaml", f"{CASSANDRA_CONFIG}/cassandra.yaml.bak")

if not path.exists(f"{CASSANDRA_CONFIG}/jvm-server.options.bak"):
    shutil.copy(f"{CASSANDRA_CONFIG}/jvm-server.options", f"{CASSANDRA_CONFIG}/jvm-server.options.bak")

# Now, modify the cassandra.yaml
with open(f"{CASSANDRA_CONFIG}/cassandra.yaml") as f:
    config = yaml.safe_load(f)

# Update some terms accordingly.
config["rpc_address"] = "0.0.0.0"
config["listen_address"] = args.listen_ip
config["broadcast_address"] = args.listen_ip
config["broadcast_rpc_address"] = args.listen_ip
config["seed_provider"][0]["parameters"][0]["seeds"] = f"{args.listen_ip}:7000"
config["concurrent_reads"] = args.reader_count
config["concurrent_counter_writes"] = args.reader_count
config["concurrent_writes"] = args.writer_count
config["row_cache_size"] = args.row_cache
config["row_cache_save_period"] = "1h"

if args.seed_server_ip:
    config["seed_provider"][0]["parameters"][0]["seeds"] = f"{args.seed_server_ip}:7000"
    print(f"A regular server is listened on {args.listen_ip} and will search for seed at {args.seed_server_ip}.")
else:
    print(f"A seed server is listened on {args.listen_ip}")

# Dump the file
with open(f"{CASSANDRA_CONFIG}/cassandra.yaml", "w") as f:
    yaml.safe_dump(config, f)

# Then, process the jvm.options
with open(f"{CASSANDRA_CONFIG}/jvm-server.options") as f:
    jvm_options = f.readlines()

if args.heap_size:  
    # Clean any old settings
    for idx, l in enumerate(jvm_options):
        if l.startswith("-Xms"):
            jvm_options[idx] = ""
        if l.startswith("-Xmx"):
            jvm_options[idx] = ""

    # Add heap size
    jvm_options.append(f"-Xms{args.heap_size}G\n")
    jvm_options.append(f"-Xmx{args.heap_size}G\n")

if args.affinity:
    found = False
    for idx, l in enumerate(jvm_options):
        if l.startswith("-Dcassandra.available_processors"):
            jvm_options[idx] = f"-Dcassandra.available_processors={args.affinity}\n"
            found = True
    if not found:
        jvm_options.append("-Dcassandra.available_processors={args.affinity}\n")

# Write it back
with open(f"{CASSANDRA_CONFIG}/jvm-server.options", "w") as f:
    f.writelines(jvm_options)

os.execvp("cassandra", ["cassandra", "-R", "-f"])

