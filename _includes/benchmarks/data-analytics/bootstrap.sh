#!/bin/bash

$HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

rm /tmp/*.pid

# installing libraries if any - (resource urls added comma separated to the ACP system variable)
cd $HADOOP_PREFIX/share/hadoop/common ; for cp in ${ACP//,/ }; do  echo == $cp; curl -LO $cp ; done; cd -


service ssh start
$HADOOP_PREFIX/sbin/start-dfs.sh
$HADOOP_PREFIX/sbin/start-yarn.sh

cd /data

if [ -e enwiki-20100904-pages-articles1.xml.bz2 ]
then
  echo "unzip the training data file ..." 
  bzip2 -d enwiki-20100904-pages-articles1.xml.bz2 > $MAHOUT_HOME/examples/temp/enwiki-20100904-pages-articles1.xml
else
  echo "No data training file, please run the data container."
  exit
fi

echo "extract training data..."
cd $MAHOUT_HOME/examples/temp/
ls
cd /data

if [ -e enwiki-latest-pages-articles.xml.bz2 ] 
then 
  echo "unzip the data file ..." 
  bzip2 -d enwiki-latest-pages-articles.xml.bz2 > $MAHOUT_HOME/examples/temp/enwiki-latest-pages-articles.xml
else
  echo "No data file, please run the data container."
  exit
fi
 

cd $MAHOUT_HOME/examples/temp/
./run.sh


if [[ $1 == "-d" ]]; then
  while true; do sleep 1000; done
  fi

if [[ $1 == "-bash" ]]; then
  /bin/bash
fi 
