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
# If no value provided, the script tries to find the primary IP address by itself. = get_ip()
parser.add_argument("--listen-addresses", "-a", help="The listening IP address of PostGRES.", default="'*'")
parser.add_argument("--number", "-n", type=int, help="The number is not used, place holder for new argument.", default=0)

args, unknown = parser.parse_known_args()

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
                new_lines.append(f"{key} = {config_dict[key][0]}")
            else:
                new_lines.append(f"#{key} = {config_dict[key][0]}")
        else:
            new_lines.append(line)

    new_config = "\n".join(new_lines)
    return new_config

POSTGRE_HOMEDIR = os.environ["POSTGRE_HOME"]

# Backup the original file
if not path.exists(f"{POSTGRE_HOMEDIR}/postgresql.conf"):
    shutil.copy(f"{POSTGRE_HOMEDIR}/postgresql.conf", f"{POSTGRE_HOMEDIR}/postgresql.conf.bak")

with open(f"{POSTGRE_HOMEDIR}/postgresql.conf", "r") as f:
    lines = f.readlines()
    config_dict = get_dict(lines)

    # Update the desired key with the new value
    config_dict["listen_addresses"] = (args.listen_addresses, True) # sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/15/main/postgresql.conf

    file_txt = save_dict(config_dict, lines)
    # Write it back
    with open(f"{POSTGRE_HOMEDIR}/postgresql.conf", "w") as f:
        f.writelines(file_txt)

os.system("service postgresql start")
os.system("sudo -u postgres psql -c \"CREATE USER cloudsuite WITH PASSWORD 'cloudsuite';\"") # Create the user called `cloudsuite`
os.system("sudo -u postgres psql -c \"CREATE DATABASE sbtest;\"") # Create a table named sbtest
os.system("sudo -u postgres psql -c \"GRANT ALL PRIVILEGES ON DATABASE sbtest TO cloudsuite\"") # Gave permission to this table
os.system("sudo -u postgres psql sbtest -c \"GRANT ALL ON SCHEMA public TO cloudsuite;\"")
os.system("sudo -u postgres psql")
