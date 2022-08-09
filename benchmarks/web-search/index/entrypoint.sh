#!/bin/bash

$SOLR_HOME/bin/solr start -force

$SOLR_HOME/bin/solr create -c nutch -d nutch -force

cd $NUTCH_HOME
$NUTCH_HOME/bin/nutch inject crawl/crawldb $NUTCH_HOME/urls

sleep infinity
