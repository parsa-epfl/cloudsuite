#! bin/bash

export JAVA_HOME=$(dirname $(dirname $(readlink -f $(which javac))))

cd $NUTCH_HOME
$NUTCH_HOME/bin/nutch generate crawl/crawldb crawl/segments -topN 100
segLast=`ls -d crawl/segments/2* | tail -1`
echo $segLast
$NUTCH_HOME/bin/nutch fetch $segLast
$NUTCH_HOME/bin/nutch parse $segLast
$NUTCH_HOME/bin/nutch updatedb crawl/crawldb $segLast
$NUTCH_HOME/bin/nutch invertlinks crawl/linkdb -dir crawl/segments
$NUTCH_HOME/bin/nutch index crawl/crawldb/ -linkdb crawl/linkdb/ -dir crawl/segments -filter -normalize -deleteGone
