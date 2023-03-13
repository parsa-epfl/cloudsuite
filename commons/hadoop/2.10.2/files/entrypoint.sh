#!/bin/bash

read -r -d '' USAGE << EOS
Usage: $0 master [MASTER_IP]
       $0 slave [MASTER_HOSTNAME] [SLAVE_IP]

MASTER_HOSTNAME is "master" by default.

IPs are needed only when using host network option with Docker in order to
correctly setup /etc/hosts for Hadoop.
EOS

case $1 in
  master)
    # start sshd
    service ssh start
    # update Hadoop config with master hostname
    HOSTNAME=`hostname`
    sed -i "s/master/$HOSTNAME/g" $HADOOP_HOME/etc/hadoop/core-site.xml
    sed -i "s/master/$HOSTNAME/g" $HADOOP_HOME/etc/hadoop/yarn-site.xml
    # update /etc/hosts with IP (only needed when using host networking)
    if [[ $2 ]]; then
      cp /etc/hosts /tmp/hosts
      sed -i "s/127.0.1.1/$2/g" /tmp/hosts
      cp /tmp/hosts /etc/hosts
    fi
    # start Hadoop daemons
    $HADOOP_HOME/bin/hdfs namenode -format cc
    $HADOOP_HOME/sbin/hadoop-daemon.sh --config $HADOOP_HOME/etc/hadoop --script hdfs start namenode
    $HADOOP_HOME/sbin/yarn-daemon.sh --config $HADOOP_HOME/etc/hadoop start resourcemanager
    $HADOOP_HOME/sbin/mr-jobhistory-daemon.sh --config $HADOOP_HOME/etc/hadoop start historyserver
    ;;
  slave)
    # start sshd
    service ssh start
    # update Hadoop config with master hostname
    HOSTNAME=${2:-master}
    sed -i "s/master/$HOSTNAME/g" $HADOOP_HOME/etc/hadoop/core-site.xml
    sed -i "s/master/$HOSTNAME/g" $HADOOP_HOME/etc/hadoop/yarn-site.xml
    # update /etc/hosts with IP (only needed when using host networking)
    if [[ $3 ]]; then
      cp /etc/hosts /tmp/hosts
      sed -i "s/127.0.1.1/$3/g" /tmp/hosts
      cp /tmp/hosts /etc/hosts
    fi
    # start Hadoop daemons
    $HADOOP_HOME/sbin/hadoop-daemon.sh --config $HADOOP_HOME/etc/hadoop --script hdfs start datanode
    $HADOOP_HOME/sbin/yarn-daemon.sh --config $HADOOP_HOME/etc/hadoop start nodemanager
    ;;
  *)
    echo "$USAGE"
    ;;
esac

# keep container running and show logs
tail -f $HADOOP_LOG_DIR/*
