#!/bin/bash
set -e
set -x

if [ ! -f /oss-performance/cmd.sh ]; then
	echo "cmd.sh not found"
	echo "Please use -v /<path>/cmd.sh:/oss-performance/cmd.sh while running the docker"
	exit 1
fi

mysql_param=($(sed -n '1p' cmd.sh))
cmd=$(sed -n '2p' cmd.sh)

mysql -h${mysql_param[0]} -u${mysql_param[1]} -p${mysql_param[2]} -e "SET GLOBAL max_connections = 1001;"
mysql -h${mysql_param[0]} -u${mysql_param[1]} -p${mysql_param[2]} -e "show variables like 'max_connections';"

eval $cmd
