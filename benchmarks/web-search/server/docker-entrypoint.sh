#!/bin/bash

#Read the server's parameters
export SERVER_HEAP_SIZE=$1 &&
  export NUM_SERVERS=$2

#Prepare Solr
export JAVA_HOME=$(dirname $(dirname $(readlink -f $(which javac))))
export SOLR_JAVA_HOME=$JAVA_HOME
$SOLR_HOME/bin/solr start -force -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -force -c cloudsuite_web_search -d cloudsuite_web_search -shards $NUM_SERVERS -p $SOLR_PORT

kill -9 $(pgrep java)

# Wait for the process to finish.
while kill -0 $(pgrep java); do
  sleep 1
done 

cd $SOLR_CORE_DIR/cloudsuite_web_search*
rm -rf data
# Copy data from dataset to server
ln -s /download/index_14GB/data data


echo "================================="
echo "Index Node IP Address: "$(hostname -I)
echo "================================="

#Run Solr
$SOLR_HOME/bin/solr start -force -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE
