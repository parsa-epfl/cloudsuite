#!/bin/bash


if [[ $# -ne 2 && $# -ne 4 ]]; then
    echo "Illegal number of parameters"
    exit 1
fi
#Read the server's parameters
export SERVER_HEAP_SIZE=$1 \
  && export NUM_SERVERS=$2

#Prepare Solr
$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d _default -shards $NUM_SERVERS -p $SOLR_PORT


if [[ "$#" -ne 2 && $3 == 'generate' ]]; then
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
    bzip2 -dc $DUMPNAME.xml.bz2 > wiki_dump.xml
    echo "</mediawiki>" >> wiki_dump.xml
    curl "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=full-import"
    
    status=`curl -s "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=status" | sed -n 's/^ *\"status\":\"//p'  | sed 's/".*//'`
    while [[ $status == 'busy' ]];
    do
	echo $status
	curl -s "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=status"
	sleep 5
	status=`curl -s "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=status" | sed -n 's/^ *\"status\":\"//p'  | sed 's/".*//'`
    done
    
    echo $status
fi

$SOLR_HOME/bin/solr stop -all




echo "================================="
echo "Index Node IP Address: "`hostname -I`
echo "================================="
  
#Run Solr  
$SOLR_HOME/bin/solr start -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
