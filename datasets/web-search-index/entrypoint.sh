#!/bin/bash
cd /home/solr
wget $INDEX_URL
tar xvzf $INDEX_NAME.tar.gz
rm $INDEX_NAME.tar.gz
mv $INDEX_NAME/data $INDEX_PATH/data
