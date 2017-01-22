#!/bin/bash

RED='\033[0;31m'
RESET='\033[0m'

${HADOOP_PREFIX}/bin/hdfs dfs -test -e /user/root/wiki
if [ $? -ne 0 ]; then
  ${HADOOP_PREFIX}/bin/hdfs dfs -mkdir -p /user/root
  ${HADOOP_PREFIX}/bin/hdfs dfs -put /root/wiki /user/root/
fi

START=$(($(date +"%s%N")/1000000))

# Create sequence files from wiki
echo -e "${RED}Mahout: seqwiki${RESET}"
${MAHOUT_HOME}/bin/mahout seqwiki -c /root/categories -i /user/root/wiki -o /user/root/wiki-seq
# Convert sequence files to vectors using bigrams
echo -e "${RED}Mahout: seq2sparse${RESET}"
${MAHOUT_HOME}/bin/mahout seq2sparse -i /user/root/wiki-seq -o /user/root/wiki-vectors -lnorm -nv -wt tfidf -ow -ng 2
# Create training and holdout sets with a random 80-20 split of the generated vector dataset
echo -e "${RED}Mahout: split${RESET}"
${MAHOUT_HOME}/bin/mahout split -i /user/root/wiki-vectors/tfidf-vectors --trainingOutput /user/root/training \
                                --testOutput /user/root/testing -rp 20 -ow -seq -xm sequential
# Train Bayes model
echo -e "${RED}Mahout: trainnb${RESET}"
${MAHOUT_HOME}/bin/mahout trainnb -i /user/root/training -o /user/root/model -li /user/root/labelindex -ow -c
# Test on holdout set
echo -e "${RED}Mahout: testnb${RESET}"
${MAHOUT_HOME}/bin/mahout testnb -i /user/root/testing -m /user/root/model -l /user/root/labelindex -ow -o /user/root/output -seq

END=$(($(date +"%s%N")/1000000))
TIME=$(($END - $START))
echo -e "\nBenchmark time: ${TIME}ms"

