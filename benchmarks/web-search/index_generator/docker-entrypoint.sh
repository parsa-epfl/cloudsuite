#!/usr/bin/env bash
if [ "$#" -ne 1 ]; then
    echo "Illegal number of parameters"
    echo "usage: Command no_pages"
    exit 1
fi
NUM_SERVERS=1
SERVER_HEAP_SIZE=12g
cd ~
NO_PAGES=$1
DUMPNAME=dump_$NO_PAGES

BYTE_OFFSET=`sed "${NO_PAGES}q;d" ~/wiki_dump/enwiki-latest-pages-articles-multistream-index.txt | sed "s/:.*//"`
echo $BYTE_OFFSET

head -c $BYTE_OFFSET ~/wiki_dump/enwiki-latest-pages-articles-multistream.xml.bz2 > $DUMPNAME.xml.bz2
bzip2 -dc $DUMPNAME.xml.bz2 > wiki_dump.xml
echo "</mediawiki>" >> wiki_dump.xml

$SOLR_HOME/bin/solr start -cloud -p $SOLR_PORT -s $SOLR_CORE_DIR -m $SERVER_HEAP_SIZE 
$SOLR_HOME/bin/solr status
$SOLR_HOME/bin/solr create_collection -c cloudsuite_web_search -d _default -shards $NUM_SERVERS -p $SOLR_PORT




curl "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=full-import"

bash
