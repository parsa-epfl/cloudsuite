#!/bin/bash

#Read the server's parameters
export SERVER_HEAP_SIZE=$1 \
  && export NUM_SERVERS=$2

#Prepare Solr
$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d basic_configs -shards $NUM_SERVERS -p $SOLR_PORT
kill -9 $(pgrep java) $(pgrep java)

#Download the index
wget -O - $INDEX_URL \
  | tar zxvf - -C $SOLR_CORE_DIR/cloudsuite_web_search*

echo "================================="
echo "Index Node IP Address: "`ifconfig eth0 2>/dev/null|awk '/inet addr:/ {print $2}'|sed 's/addr://'`
echo "================================="
  
#Run Solr  
$SOLR_HOME/bin/solr start -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
