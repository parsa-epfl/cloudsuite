#!/bin/bash

if [ "$WORKLOAD" = "django" ]
then
	echo "django"
	service cassandra start && tail -f /dev/null
else
	echo "datacaching"
	chmod +x /scripts/dataserving_config.sh
        /scripts/dataserving_config.sh cassandra
fi
