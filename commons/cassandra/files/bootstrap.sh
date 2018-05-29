#!/bin/bash

if [ "$WORKLOAD" = "django" ]
then
	# django-workload
	/scripts/init_config.sh cassandra
	service cassandra start && tail -f /dev/null
else
	# data-serving workload
	chmod +x /scripts/dataserving_config.sh
        /scripts/dataserving_config.sh cassandra
fi
