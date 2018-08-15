#!/bin/bash


if [[ $# -ne 2 && $# -ne 4 ]]; then
    echo "Illegal number of parameters"
    exit 1
fi
#Read the server's parameters
export SERVER_HEAP_SIZE=$1 \
  && export NUM_SERVERS=$2

#Prepare Solr
INDEX_PATH=/home/solr/index_data


if [[ "$#" -ne 2 && $3 == 'generate' ]]; then
    $SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
    $SOLR_HOME/bin/solr status
    $SOLR_HOME/bin/solr create_collection -c index_creator -d _default -shards 1 -p $SOLR_PORT
    DUMP_FOLDER=~/wiki_dump
    INDEX_FILE=$DUMP_FOLDER/enwiki-latest-pages-articles-multistream-index.txt
    DUMP_FILE=$DUMP_FOLDER/enwiki-latest-pages-articles-multistream.xml.bz2
    cd /home/solr
    echo "Generating Index"
    NO_PAGES=$4
    DUMPNAME=dump_$NO_PAGES
    echo "Num of Pages = $NO_PAGES"
    if [ ! -f $INDEX_FILE ]; then
	echo "$INDEX_FILE not found!"
	exit 1
    fi

    if [ ! -f $DUMP_FILE ]; then
	echo "$DUMP_FILE not found!"
	exit 1
    fi 

    
    if [[ $NO_PAGES -ge `wc -l < $INDEX_FILE` ]]; then
	echo "wikipedia dump does not have $NO_PAGES pages"
	exit 1
    fi
   
    
    BYTE_OFFSET=`sed "${NO_PAGES}q;d" $INDEX_FILE | sed "s/:.*//"`
    

    head -c $BYTE_OFFSET $DUMP_FILE > $DUMPNAME.xml.bz2
    pbzip2 -dc $DUMPNAME.xml.bz2 > wiki_dump.xml
    rm $DUMPNAME.xml.bz2
    echo "</mediawiki>" >> wiki_dump.xml
    curl "http://localhost:8983/solr/index_creator/dataimport?command=full-import"
    
    status=`curl -s "http://localhost:8983/solr/index_creator/dataimport?command=status" | sed -n 's/^ *\"status\":\"//p'  | sed 's/".*//'`
    while [[ $status == 'busy' ]];
    do
	echo $status
	curl -s "http://localhost:8983/solr/index_creator/dataimport?command=status"
	sleep 5
	status=`curl -s "http://localhost:8983/solr/index_creator/dataimport?command=status" | sed -n 's/^ *\"status\":\"//p'  | sed 's/".*//'`
    done
    

    mkdir $INDEX_PATH
    rm /home/solr/wiki_dump.xml
    mv $SOLR_CORE_DIR/index_creator_shard1_replica_n1/data $INDEX_PATH/data
    $SOLR_HOME/bin/solr delete -c index_creator
    $SOLR_HOME/bin/solr stop -all
fi

if [ ! -d $INDEX_PATH/data ]; then
	echo "$INDEX_PATH/data/ not found!"
	exit 1
fi 

$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d _default -shards $NUM_SERVERS -p $SOLR_PORT
$SOLR_HOME/bin/solr stop -all

echo $SOLR_CORE_DIR/cloudsuite_web_search* | xargs -n 1 cp -r $INDEX_PATH/data 



echo "================================="
echo "Index Node IP Address: "`hostname -I`
echo "================================="
  
#Run Solr  
$SOLR_HOME/bin/solr start -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
