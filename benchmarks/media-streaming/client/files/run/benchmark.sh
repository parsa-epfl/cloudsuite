#!/bin/bash

num_clients_per_machine=4
min_num_sessions=100
max_num_sessions=2000

streaming_client_dir=..
#server_ip=$(tail -n 1 hostlist.server)
server_ip=$1

peak_hunter/launch_hunt_bin.sh                     \
	$server_ip                                 \
	hostlist.client                            \
	$streaming_client_dir                      \
	$num_clients_per_machine                   \
	$min_num_sessions                          \
	$max_num_sessions

./process_logs.sh
