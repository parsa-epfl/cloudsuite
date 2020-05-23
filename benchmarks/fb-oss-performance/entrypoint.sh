#!/bin/bash
set -ex

if [ ! -f /oss-performance/cmd.sh ]; then
	echo "cmd.sh not found"	
	echo "Please use -v /<path>/cmd.sh:/oss-performance/cmd.sh while running the docker"	
	exit 1	
fi

bash /oss-performance/cmd.sh
# Uncomment the below line and build, while running perf
# cat /oss-performance/nohup.out
