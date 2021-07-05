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
    sed -i "s/master/$HOSTNAME/g" $HADOOP_PREFIX/etc/hadoop/core-site.xml
    sed -i "s/master/$HOSTNAME/g" $HADOOP_PREFIX/etc/hadoop/yarn-site.xml
    # update /etc/hosts with IP (only needed when using host networking)
    if [ -n $2 ]; then
      cp /etc/hosts /tmp/hosts
      sed -i "s/127.0.1.1/$2/g" /tmp/hosts
      cp /tmp/hosts /etc/hosts
    fi
    # start Hadoop daemons
    $HADOOP_PREFIX/bin/hdfs namenode -format cc
    $HADOOP_PREFIX/sbin/hadoop-daemon.sh --config $HADOOP_PREFIX/etc/hadoop --script hdfs start namenode
    $HADOOP_PREFIX/sbin/yarn-daemon.sh --config $HADOOP_PREFIX/etc/hadoop start resourcemanager
    $HADOOP_PREFIX/sbin/mr-jobhistory-daemon.sh --config $HADOOP_PREFIX/etc/hadoop start historyserver
    ;;
  slave)
    # start sshd
    service ssh start
    # update Hadoop config with master hostname
    HOSTNAME=${2:-master}
    sed -i "s/master/$HOSTNAME/g" $HADOOP_PREFIX/etc/hadoop/core-site.xml
    sed -i "s/master/$HOSTNAME/g" $HADOOP_PREFIX/etc/hadoop/yarn-site.xml
    # update /etc/hosts with IP (only needed when using host networking)
    if [ -n $3 ]; then
      cp /etc/hosts /tmp/hosts
      sed -i "s/127.0.1.1/$3/g" /tmp/hosts
      cp /tmp/hosts /etc/hosts
    fi
    # start Hadoop daemons
    $HADOOP_PREFIX/sbin/hadoop-daemon.sh --config $HADOOP_PREFIX/etc/hadoop --script hdfs start datanode
    $HADOOP_PREFIX/sbin/yarn-daemon.sh --config $HADOOP_PREFIX/etc/hadoop start nodemanager
    ;;
  *)
    echo "$USAGE"
    ;;
esac

# keep container running and show logs
tail -f $HADOOP_LOG_DIR/*
