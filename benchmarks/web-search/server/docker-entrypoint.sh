#!/bin/bash

#Read the server's parameters
export SERVER_HEAP_SIZE=$1 &&
  export NUM_SERVERS=$2

#Prepare Solr
$SOLR_HOME/bin/solr start -force -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE
$SOLR_HOME/bin/solr status
#$SOLR_HOME/bin/solr create_collection -force -c cloudsuite_web_search -d basic_configs -shards $NUM_SERVERS -p $SOLR_PORT
#$SOLR_HOME/bin/solr create_collection -force -c cloudsuite_web_search -d sample_techproducts_configs -shards $NUM_SERVERS -p $SOLR_PORT
#$SOLR_HOME/bin/solr create_collection -force -c cloudsuite_web_search -d _default -shards $NUM_SERVERS -p $SOLR_PORT


$SOLR_HOME/bin/solr create_collection -force -c cloudsuite_web_search -d cloudsuite_web_search -shards $NUM_SERVERS -p $SOLR_PORT

kill -9 $(pgrep java) $(pgrep java)

cd $SOLR_CORE_DIR/cloudsuite_web_search*
rm -rf data
# Copy data from dataset to server
#cp --verbose -a /download/data .
cp --verbose -a /root/data .
#rm -r /download/*

echo "================================="
echo "Index Node IP Address: "$(hostname -I)
echo "================================="

#Run Solr
$SOLR_HOME/bin/solr start -force -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE
