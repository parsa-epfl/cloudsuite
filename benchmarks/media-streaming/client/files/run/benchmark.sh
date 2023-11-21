#!/bin/bash

server_ip=$1
num_clients_per_machine=${2:-4}
num_sessions=${3:-100}
rate=${4:-10}
mode=${5:-TLS}
num_concurrent_sessions=${6:-0}

streaming_client_dir=..
#server_ip=$(tail -n 1 hostlist.server)

peak_hunter/launch_hunt_bin.sh           \
	$server_ip                             \
	hostlist.client                        \
	$streaming_client_dir                  \
	$num_clients_per_machine               \
	$num_sessions                          \
	$rate                                  \
    $mode                                  \
    $num_concurrent_sessions

./process_logs.sh
