#!/bin/bash
set -e

echo Server IP
ifconfig eth0 2>/dev/null | awk '/inet addr:/ {print $2}' | sed 's/addr://'

if [ -z "$CASSANDRA_SEEDS" ]; then
    NEED_INIT=1
    echo Running Cassandra seed server.
else
    NEED_INIT=0
    echo Running regular Cassandra server.
fi

# first arg is `-f` or `--some-option`
if [ "${1:0:1}" = '-' ]; then
	set -- cassandra -f "$@"
fi

if [ "$1" = 'cassandra' ] || [ "$1" = 'bash' ]; then
	: ${CASSANDRA_RPC_ADDRESS='0.0.0.0'}

	: ${CASSANDRA_LISTEN_ADDRESS='auto'}
	if [ "$CASSANDRA_LISTEN_ADDRESS" = 'auto' ]; then
		CASSANDRA_LISTEN_ADDRESS="$(hostname --ip-address)"
	fi

	: ${CASSANDRA_BROADCAST_ADDRESS="$CASSANDRA_LISTEN_ADDRESS"}

	if [ "$CASSANDRA_BROADCAST_ADDRESS" = 'auto' ]; then
		CASSANDRA_BROADCAST_ADDRESS="$(hostname --ip-address)"
	fi
	: ${CASSANDRA_BROADCAST_RPC_ADDRESS:=$CASSANDRA_BROADCAST_ADDRESS}

	if [ -n "${CASSANDRA_NAME:+1}" ]; then
		: ${CASSANDRA_SEEDS:="cassandra"}
	fi
	: ${CASSANDRA_SEEDS:="$CASSANDRA_BROADCAST_ADDRESS"}
	
	sed -ri 's/(- seeds:) "127.0.0.1"/\1 "'"$CASSANDRA_SEEDS"'"/' "$CASSANDRA_CONFIG/cassandra.yaml"

	for yaml in \
		broadcast_address \
		broadcast_rpc_address \
		cluster_name \
		endpoint_snitch \
		listen_address \
		num_tokens \
		rpc_address \
		start_rpc \
	; do
		var="CASSANDRA_${yaml^^}"
		val="${!var}"
		if [ "$val" ]; then
			sed -ri 's/^(# )?('"$yaml"':).*/\2 '"$val"'/' "$CASSANDRA_CONFIG/cassandra.yaml"
		fi
	done

	for rackdc in dc rack; do
		var="CASSANDRA_${rackdc^^}"
		val="${!var}"
		if [ "$val" ]; then
			sed -ri 's/^('"$rackdc"'=).*/\1 '"$val"'/' "$CASSANDRA_CONFIG/cassandra-rackdc.properties"
		fi
	done
fi

"$@"

exit=0

if [ $NEED_INIT -eq 1 ]; then
    echo ======================================================
    echo Create a usertable for the seed server
    echo ======================================================
    while [ $exit -eq 0 ]; do
        set +e
        cqlsh -f /setup_tables.txt localhost
        if [[ "$?" -eq 0 ]]; then
            exit=1
        else
            echo Cannot connect to the seed server. Trying again...
        fi
        set -e
        sleep 5
    done

    printf "========\n--------------Keyspace usertable was created--------------\n========\n"
else
    echo "Cassandra seed server exists"
fi

while true; do
    sleep 1;
done
