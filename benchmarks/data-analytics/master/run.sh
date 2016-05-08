#!/bin/bash

#set the number of slaves
cd $HADOOP_PREFIX/etc/hadoop
echo "Type the number of slave nodes, followed by [ENTER]:"
read nslaves
for i in `seq 1 $nslaves`;
do
	echo "slave$i.cloudsuite.com" >> slaves
done;

#Preparing Hadoop
hdfs namenode -format
service ssh start
$HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

#Set the memory for mahout
export HADOOP_CLIENT_OPTS="-Xmx20192m"
rm /tmp/*.pid

# installing libraries if any - (resource urls added comma separated to the ACP system variable)
cd $HADOOP_PREFIX/share/hadoop/common ; for cp in ${ACP//,/ }; do  echo == $cp; curl -LO $cp ; done; cd -

# Start Hadoop
$HADOOP_PREFIX/sbin/start-dfs.sh
$HADOOP_PREFIX/sbin/start-yarn.sh

# Create a workdir for mahout
export WORK_DIR=${MAHOUT_HOME}/examples/temp/mahout-work-wiki
mkdir -p ${WORK_DIR}
cd $WORK_DIR
mkdir wikixml
cd wikixml

if [ -e enwiki-latest-pages-articles.xml ] 
then
  echo "The dataset is available."
elif [ -e enwiki-latest-pages-articles.xml.bz2 ]
then
  echo "unzip the data file ..." 
  bzip2 -d enwiki-latest-pages-articles.xml.bz2
else
  echo "Please select a number to choose the dataset size: 1. Partial small (149MB zipped), 2. Partial larger (317MB zipped), 3. Full wikipedia (10GB zipped)"
  read -p "Enter your choice : " choice
  if [ "$choice" == "1" ]
  then
  echo "Getting the partial small dataset... It takes time..."
  curl https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles1.xml-p000000010p000030302.bz2 -o ${WORK_DIR}/wikixml/enwiki-latest-pages-articles.xml.bz2
  elif [ "$choice" == "2" ]
  then
  echo "Getting the partial larger dataset... It takes time..."
  curl https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles10.xml-p002336425p003046511.bz2 -o ${WORK_DIR}/wikixml/enwiki-latest-pages-articles.xml.bz2
  else
  echo "Getting the full dataset... It takes time..."
  curl https://dumps.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles.xml.bz2 -o ${WORK_DIR}/wikixml/enwiki-latest-pages-articles.xml.bz2
  fi
  echo "unzip the data file ..." 
  bzip2 -d enwiki-latest-pages-articles.xml.bz2
fi

# Put the dataset to HDFS 
hdfs dfs -rm ${WORK_DIR}/wikixml
hdfs dfs -mkdir -p ${WORK_DIR}
hdfs dfs -put ${WORK_DIR}/wikixml ${WORK_DIR}/wikixml

#run the algorithm
echo "Creating sequence files from wikiXML"
mahout seqwiki -c ${MAHOUT_HOME}/examples/temp/categories.txt -i ${WORK_DIR}/wikixml/enwiki-latest-pages-articles.xml -o ${WORK_DIR}/wikipediainput

echo "Converting sequence files to vectors using bigrams"
mahout seq2sparse -i ${WORK_DIR}/wikipediainput -o ${WORK_DIR}/wikipediaVecs -lnorm -nv -wt tfidf -ow -ng 2

echo "Creating training and holdout set with a random 80-20 split of the generated vector dataset"
mahout split -i ${WORK_DIR}/wikipediaVecs/tfidf-vectors --trainingOutput ${WORK_DIR}/training --testOutput  ${WORK_DIR}/testing -rp 20 -ow -seq -xm sequential 

echo "Training Bayes model"
mahout trainnb -i ${WORK_DIR}/training -o ${WORK_DIR}/model -li ${WORK_DIR}/labelindex -ow -c

echo "Testing on holdout set: Bayes"
mahout testnb -i  ${WORK_DIR}/testing -m ${WORK_DIR}/model -l ${WORK_DIR}/labelindex -ow -o ${WORK_DIR}/output -seq
