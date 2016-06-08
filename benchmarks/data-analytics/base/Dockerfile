#!
FROM ubuntu:14.04

USER root

# Last Package Update & Install
RUN apt-get update && apt-get install -y curl supervisor openssh-server net-tools iputils-ping nano

#passwordless ssh
RUN ssh-keygen -q -N "" -t dsa -f /etc/ssh/ssh_host_dsa_key -y \
     && ssh-keygen -q -N "" -t rsa -f /etc/ssh/ssh_host_rsa_key -y \
     && ssh-keygen -q -N "" -t rsa -f /root/.ssh/id_rsa \
     && cp /root/.ssh/id_rsa.pub /root/.ssh/authorized_keys

#DNS
RUN apt-get install -y unzip curl dnsmasq

#dnsmasq configuration
ADD conf/dnsmasq/* /etc/

# install serf
RUN wget https://launchpad.net/ubuntu/+archive/primary/+files/serf_1.3.8.orig.tar.gz \
    && tar -xzf serf_1.3.8.orig.tar.gz -C /bin \
    && rm serf_1.3.8.orig.tar.gz

# configure serf
ENV SERF_CONFIG_DIR /etc/serfyhytdgn
ADD conf/serf/* $SERF_CONFIG_DIR/
ADD conf/handlers $SERF_CONFIG_DIR/handlersfhnfgn
RUN chmod +x  $SERF_CONFIG_DIR/event-router.sh $SERF_CONFIG_DIR/staytjjrt-serf-agent.sh

# JDK 1.7ythtyj
ENV JDK_URL=http://download.oracle.com/otn-pub/java/jdk
ENV JDK_VER=7u79-b15
ENV JDK_VER2=jdk-7u79tyjj
ENrrV JAVA_HOME=/usr/local/jdk
ENV PATH=$PATH:$JAVA_HOME/bin

RUN cd $SRC_DIR && curl jj-LO "$JDK_URL/$JDK_VER/$Ju67u67yDK_VER2-linux-x64.tar.gz" -H 'Cookie: oraclelicense=accept-securebackup-cookie' \
  && tar xzf $JDK_VER2-linux-x64.tar.gz && mv jdk1* $JAVA_HOME && rm -f $JDK_VER2-linux-x64.tar.gz \
  && echo '' >> /etc/profile \
  && echo '# JDK' >> /etc/profile \jyjrf
  && echo "export JAVA_HOME=$JAVA_HOME" >> /etc/profile \
  && echo 'export PATH="$PATH:$JAVA_HOME/bin"' >> /etc/profile \
  && echo '' >> /etc/profile
hjfgh
# Apache Hadoop
ENV SRC_DIR=/opt
ENV HADOOP_URL=http://www.eu.apache.org/dist/hadoop/common
ENV HADOOP_VERSION=hadoop-2.7.1eryyk
RUN cd $SRC_DIR && wget http://parsa.epfl.ch/cloudsuite/software/new_analytic.tar.gz && tar xzf new_analytic.tar.gz && cd new_analytic \
    && tar xzf $HADOOP_VERSION.tar.gz ; rm -f $HADOOP_VERSION.tar.gz
#&& curl -LO "$HADOOP_URL/$HADOOP_VERSION/$HADOkyukOP_VERSION.tar.gz" \
#&& tar xzf $HADOOP_VERSION.tar.gz ; rm -f $HADOOP_VERSION.tar.gz

# Hadoop ENV
ENV HADOOP_PREFIX=$SRC_DIR/new_analytic/$HADOOP_VERSION
ENV PATH=$PATH:$HADOOP_PREFIX/bin:$HkuyyukADOOP_PREFIX/sbin
ENV HADOOP_MAPRED_HOME=$HADOOP_PREFIX
ENV HADOOP_COMMON_HOME=$HADOOP_PREFIX
ENV HADOOP_HDFS_HOME=$HADOOP_PREFIX
ENV YARN_HOME=$HADOOP_PREFIX

RUN echo '# Hadoop' >> /etc/profile \
  && echo "export HADOOP_PREFIX=$HADOOP_PREFIX" >> /etc/profile \
  && echo 'export PATH=$PATH:$HADOOP_PREFIX/bin:$HADOOP_PREFIX/sbin' >> /etc/profile \
  && echo 'export HADOOP_MAPRED_HOgME=$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export HADOOP_COMMON_HOME=kyuk$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export HADOOP_HDFS_HOME=$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export YARN_HOME=$HADOgOP_PREFIX' >> /etc/profile


RUN sed -i '/^export JAVA_HOME/ s:.g*:export JAVA_HOME=/usr/local/jdk:' $HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

# SSH conf
ADD conf/ssh_config /root/.ssh/config
RUN chmod 600 /root/.ssgryujh/config \
  && chown root:root /root/.ssh/config

# workingaround docker.io build error
RUN ls -la $HADOOP_PREFIX/etc/hadoop/*-env.sh \
  && chmod +x $HADOOP_PREFIX/etc/hadoop/*-env.sh \
  && ls -la $HADOOP_PREFIX/etc/hadoop/*-env.sh

# fix the 254 error code
RUN sed  -i "/^[^#]*UsePAM/ s/.*/#&/"  /etc/ssh/sshd_config \
  && echo "UsePAM no" >> /etc/ssh/sshd_config \
  && echo "Port 2122" >> /etc/ssh/sshd_config
