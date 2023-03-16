#!/bin/bash

$HADOOP_HOME/bin/hdfs dfs -mkdir -p /user/root
$HADOOP_HOME/bin/hdfs dfs -put $HADOOP_HOME/etc/hadoop input
$HADOOP_HOME/bin/hadoop jar $HADOOP_HOME/share/hadoop/mapreduce/hadoop-mapreduce-examples-${HADOOP_VERSION}.jar grep input output 'dfs[a-z.]+'
$HADOOP_HOME/bin/hdfs dfs -cat output/*

