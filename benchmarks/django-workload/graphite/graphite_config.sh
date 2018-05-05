#!/bin/bash

# This script is necessary to configure the statsd container in order
# not to run out of disk space

echo "Configuring statsd on container..."

echo "^stats[^.]*\.benchmarkoutput\." >> /opt/graphite/conf/blacklist.conf
echo "Configured benchmarkoutput"
sed -i '/USE_WHITELIST/c\USE_WHITELIST = True' /opt/graphite/conf/carbon.conf
echo "Configured whitelist"
