#!/bin/bash

# install cassandra and place it to some place.
apt update 
apt install -y --no-install-recommends procps python3 iproute2 numactl wget

# Download files from Cassandra archive
wget --show-progress "https://archive.apache.org/dist/cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz" -O /tmp/cassandra.tar.gz
wget --show-progress "https://archive.apache.org/dist/cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz.sha512" -O /tmp/cassandra.tar.gz.sha512

# Verify the file
echo "$(cat /tmp/cassandra.tar.gz.sha512) /tmp/cassandra.tar.gz" | sha512sum --check --strict

if [ $? -gt 0 ]; then
    echo "Verification not pass. The file is corrupted".
    exit -1;
fi

rm /tmp/cassandra.tar.gz.sha512

# Extract file
mkdir -p $CASSANDRA_HOME
tar -xvf /tmp/cassandra.tar.gz -C $CASSANDRA_HOME --strip-components 1
rm /tmp/cassandra.tar.gz

# Create the config folder
mv "$CASSANDRA_HOME/conf" "$CASSANDRA_CONFIG"

ln -sT "$CASSANDRA_CONFIG" "$CASSANDRA_HOME/conf"

mkdir -p "$CASSANDRA_CONFIG" /var/lib/cassandra /var/log/cassandra
ln -sT /var/lib/cassandra "$CASSANDRA_HOME/data"
ln -sT /var/log/cassandra "$CASSANDRA_HOME/logs"

# Remove apt cacheh file
rm -rf /var/lib/apt/lists/*
