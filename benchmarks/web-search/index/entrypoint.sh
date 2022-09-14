#!/bin/bash

export JAVA_HOME=$(dirname $(dirname $(readlink -f $(which javac))))
export SOLR_JAVA_HOME=$JAVA_HOME

$SOLR_HOME/bin/solr start -force

$SOLR_HOME/bin/solr create -c nutch -d nutch -force

cd $NUTCH_HOME
$NUTCH_HOME/bin/nutch inject crawl/crawldb $NUTCH_HOME/urls

sleep infinity
