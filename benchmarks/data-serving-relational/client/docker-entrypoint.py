#!/usr/bin/env python3

import os
import sys
import subprocess
import argparse

args = sys.argv[1:]
parser = argparse.ArgumentParser()
parser.add_argument("--tpcc", help="Run TPC-C benchmark", action='store_true')
parser.add_argument("--oltp-rw", help="Run sysbench OLTP Read/Write workload", action='store_true')
parser.add_argument("--server-ip", help="IP of the server to load")

args_parsed, unknown = parser.parse_known_args()

if not args_parsed.server_ip:
    print("Please pass the server IP as an argument with --server-ip=<IP>")
    sys.exit()

print("args: " + str(args))
if not args_parsed.tpcc and not args_parsed.oltp_rw:
    print("Precise whenever it's --tpcc or --oltp-rw")
    sys.exit()

import os
import os.path as path
import shutil

def get_dict(lines):
    config_dict = {}
    for line in lines:
        is_enabled = True 
        if "=" in line:
            if line.startswith("#"):
                is_enabled = False
                line = line[1:] # Remove `#`

            key, value = line.split("=", 1)
            key = key.strip()
            value = value.strip()
            config_dict[key] = (value, is_enabled)

    return config_dict

def save_dict(config_dict, lines):
    # Reconstruct the updated configuration
    new_lines = []
    for line in lines:
        if "=" in line:
            if line.startswith("#"):
                line = line[1:]
            key, _ = line.split("=", 1)
            key = key.strip()
            if config_dict[key][1]:
                new_lines.append(f"{key}={config_dict[key][0]}")
            else:
                new_lines.append(f"#{key}={config_dict[key][0]}")
        else:
            new_lines.append(line)

    new_config = "\n".join(new_lines)
    return new_config

DATABASE_CONF_FILE = os.environ["DATABASE_CONF_FILE"]

if not path.exists(f"{DATABASE_CONF_FILE}"):
    shutil.copy(f"{DATABASE_CONF_FILE}", f"{DATABASE_CONF_FILE}.bak")

with open(f"{DATABASE_CONF_FILE}", "r") as f:
    lines = f.readlines()
    config_dict = get_dict(lines)

    # Update the desired key with the new value
    config_dict["pgsql-host"] = (args_parsed.server_ip, True)

    file_txt = save_dict(config_dict, lines)
    # Write it back
    with open(f"{DATABASE_CONF_FILE}", "w") as f:
        f.writelines(file_txt)

if args_parsed.tpcc:
    subprocess.call(['/root/template/tpcc.py'] + args)
else:
    subprocess.call(['/root/template/oltp-rw.py'] + args)