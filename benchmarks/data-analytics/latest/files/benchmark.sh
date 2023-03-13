#!/bin/bash

RED='\033[0;31m'
RESET='\033[0m'

source ~/.bashrc
echo -e "Mahout: Start HDFS server"
${HADOOP_HOME}/bin/hdfs dfs -test -e /data/wiki
if [ $? -ne 0 ]; then
  echo -e "Mahout: make dir for /user/data/wiki"
  ${HADOOP_HOME}/bin/hdfs dfs -mkdir -p /user/data
  ${HADOOP_HOME}/bin/hdfs dfs -put /data/wiki /user/data/
fi

START=$(($(date +"%s%N")/1000000))

# Create sequence files from wiki
echo -e "${RED}Mahout: seqwiki${RESET}"
${MAHOUT_HOME}/bin/mahout seqwiki -c /user/categories -i /user/data/wiki -o /user/data/wiki-seq
# Convert sequence files to vectors using bigrams
echo -e "${RED}Mahout: seq2sparse${RESET}"
${MAHOUT_HOME}/bin/mahout seq2sparse -i /user/data/wiki-seq -o /user/data/wiki-vectors -lnorm -nv -wt tfidf -ow -ng 2
# Create training and holdout sets with a random 80-20 split of the generated vector dataset
echo -e "${RED}Mahout: split${RESET}"
${MAHOUT_HOME}/bin/mahout split -i /user/data/wiki-vectors/tfidf-vectors --trainingOutput /user/data/training \
                                --testOutput /user/data/testing -rp 20 -ow -seq -xm sequential
# Train Bayes model
echo -e "${RED}Mahout: trainnb${RESET}"
${MAHOUT_HOME}/bin/mahout trainnb -i /user/data/training -o /user/data/model -li /user/data/labelindex -ow -c
# Test on holdout set
echo -e "${RED}Mahout: testnb${RESET}"
${MAHOUT_HOME}/bin/mahout testnb -i /user/data/testing -m /user/data/model -l /user/data/labelindex -ow -o /user/data/output -seq

END=$(($(date +"%s%N")/1000000))
TIME=$(($END - $START))
echo -e "\nBenchmark time: ${TIME}ms"

