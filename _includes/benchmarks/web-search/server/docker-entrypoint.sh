#!/bin/bash

$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d basic_configs -shards $NUM_SERVERS -p $SOLR_PORT
kill -9 $(pgrep java) $(pgrep java)
echo "Copying the index..."
cp -r /data/data $SOLR_CORE_DIR/cloudsuite_web_search*/.
$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
sleep 60s
$SOLR_HOME/bin/solr status
sleep 60s
$SOLR_HOME/bin/solr status
bash
