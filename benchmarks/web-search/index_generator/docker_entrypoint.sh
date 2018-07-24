#!/usr/bin/env bash
if [ "$#" -ne 1 ]; then
    echo "Illegal number of parameters"
    echo "usage: Command no_pages"
    exit 1
fi
cd ~
NO_PAGES=$1
DUMPNAME=dump_$NO_PAGES

BYTE_OFFSET=`sed "${NO_PAGES}q;d" ~/wiki_dump/enwiki-latest-pages-articles-multistream-index.txt | sed "s/:.*//"`
echo $BYTE_OFFSET

head -c $BYTE_OFFSET ~/wiki_dump/enwiki-latest-pages-articles-multistream.xml.bz2 > $DUMPNAME.xml.bz2
bzip2 -dc $DUMPNAME.xml.bz2 > wiki_dump.xml
echo "</mediawiki>" >> wiki_dump.xml


"/home/solr/solr-7.4.0/bin/solr" start -p 8983 -s "solr-7.4.0/example/example-DIH/solr"
sleep 2

curl "http://localhost:8983/solr/cloudsuite_web_search/dataimport?command=full-import"

bash
