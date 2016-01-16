#!/bin/bash

cd $HADOOP_PREFIX/etc/hadoop

echo "Type the number of slave nodes, followed by [ENTER]:"

read nslaves

for i in `seq 1 $nslaves`;
do
	echo "slave$i.cloudsuite.com" >> slaves
done;

hdfs namenode -format
service ssh start
$HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

rm /tmp/*.pid

# installing libraries if any - (resource urls added comma separated to the ACP system variable)
cd $HADOOP_PREFIX/share/hadoop/common ; for cp in ${ACP//,/ }; do  echo == $cp; curl -LO $cp ; done; cd -

$HADOOP_PREFIX/sbin/start-dfs.sh
$HADOOP_PREFIX/sbin/start-yarn.sh

cd /data

if [ -e enwiki-20100904-pages-articles1.xml.bz2 ]
then
  echo "unzip the training data file ..." 
  bzip2 -d enwiki-20100904-pages-articles1.xml.bz2 > $MAHOUT_HOME/examples/temp/enwiki-20100904-pages-articles1.xml
  mv enwiki-20100904-pages-articles1.xml $MAHOUT_HOME/examples/temp/enwiki-20100904-pages-articles1.xml
else
  echo "No data training file, please run the data container."
  exit
fi

cd $MAHOUT_HOME/examples/temp

if [ -e enwiki-latest-pages-articles.xml.bz2 ] 
then 
  echo "unzip the data file ..." 
  bzip2 -d enwiki-latest-pages-articles.xml.bz2
else
  echo "Getting the main dataset... It takes time..."
  wget http://download.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles.xml.bz2
  echo "unzip the data file ..." 
  bzip2 -d enwiki-latest-pages-articles.xml.bz2
fi

mahout org.apache.mahout.text.wikipedia.WikipediaXmlSplitter -d $MAHOUT_HOME/examples/temp/enwiki-latest-pages-articles.xml -o wikipedia/chunks -c 64
mahout org.apache.mahout.text.wikipedia.WikipediaXmlSplitter -d $MAHOUT_HOME/examples/temp/enwiki-20100904-pages-articles1.xml -o wikipedia-training/chunks -c 64
mahout org.apache.mahout.text.wikipedia.WikipediaDatasetCreatorDriver -i wikipedia/chunks -o wikipediainput -c $MAHOUT_HOME/examples/temp/categories.txt
mahout org.apache.mahout.text.wikipedia.WikipediaDatasetCreatorDriver -i wikipedia-training/chunks -o traininginput -c $MAHOUT_HOME/examples/temp/categories.txt
mahout trainclassifier -i traininginput -o wikipediamodel -mf 4 -ms 4
mahout testclassifier -m wikipediamodel -d wikipediainput --method mapreduce


