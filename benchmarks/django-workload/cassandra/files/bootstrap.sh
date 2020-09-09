#!/bin/bash

if [ -n "$SYSTEM_MEMORY" ]; then
        TWO=2
        EIGHT=8
        XMS="-Xms"$((SYSTEM_MEMORY/TWO))"G"
        XMX="-Xmx"$((SYSTEM_MEMORY/TWO))"G"
        XMN="-Xmn"$((SYSTEM_MEMORY/EIGHT))"G"

        JVM_OPTIONS_FILE="/etc/cassandra/jvm.options"

        echo "Optimising jvm parameters..."

        sed -i 's/^-Xms.*/'"$XMS"'/g' $JVM_OPTIONS_FILE
        sed -i 's/^-Xmx.*/'"$XMX"'/g' $JVM_OPTIONS_FILE
        sed -i 's/^-Xmn.*/'"$XMN"'/g' $JVM_OPTIONS_FILE
fi

if [ -n "$ENDPOINT" ]
then
    /scripts/init_config.sh $ENDPOINT
else
    /scripts/init_config.sh localhost
fi

cassandra -R && tail -f /dev/null
