#!/bin/bash
echo $#
#Read the server's parameters
if [[ $# -ne 2 && $# -ne 4 ]]; then
    echo "Illegal number of parameters"
    exit 1
fi
export SERVER_HEAP_SIZE=$1 \
  && export NUM_SERVERS=$2

#Prepare Solr
$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d _default -shards $NUM_SERVERS -p $SOLR_PORT


if [[ "$#" -ne 2 && $3 == 'generate' ]]; then
    cd /home/solr
    echo "Generating Index"
    NO_PAGES=$4
    DUMPNAME=dump_$NO_PAGES
    echo "Num of Pages = $NO_PAGES"

    BYTE_OFFSET=`sed "${NO_PAGES}q;d" ~/wiki_dump/enwiki-latest-pages-articles-multistream-index.txt | sed "s/:.*//"`
    echo $BYTE_OFFSET

    head -c $BYTE_OFFSET ~/wiki_dump/enwiki-latest-pages-articles-multistream.xml.bz2 > $DUMPNAME.xml.bz2
    bzip2 -dc $DUMPNAME.xml.bz2 > wiki_dump.xml
    echo "</mediawiki>" >> wiki_dump.xml
    curl "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=full-import"
    bash


fi
$SOLR_HOME/bin/solr stop -all




echo "================================="
echo "Index Node IP Address: "`hostname -I`
echo "================================="
  
#Run Solr  
$SOLR_HOME/bin/solr start -cloud -f -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
