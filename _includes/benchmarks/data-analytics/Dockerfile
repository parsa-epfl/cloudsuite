FROM ubuntu:14.04

USER root

# Last Package Update & Install
RUN apt-get update && apt-get install -y curl supervisor openssh-server net-tools iputils-ping nano

#passwordless ssh
RUN ssh-keygen -q -N "" -t dsa -f /etc/ssh/ssh_host_dsa_key -y
RUN ssh-keygen -q -N "" -t rsa -f /etc/ssh/ssh_host_rsa_key -y
RUN ssh-keygen -q -N "" -t rsa -f /root/.ssh/id_rsa
RUN cp /root/.ssh/id_rsa.pub /root/.ssh/authorized_keys

# JDK 1.7 
ENV JDK_URL http://download.oracle.com/otn-pub/java/jdk 
ENV JDK_VER 7u79-b15
ENV JDK_VER2 jdk-7u79
ENV JAVA_HOME /usr/local/jdk
ENV PATH $PATH:$JAVA_HOME/bin 
RUN cd $SRC_DIR && curl -LO "$JDK_URL/$JDK_VER/$JDK_VER2-linux-x64.tar.gz" -H 'Cookie: oraclelicense=accept-securebackup-cookie' \
  && tar xzf $JDK_VER2-linux-x64.tar.gz && mv jdk1* $JAVA_HOME && rm -f $JDK_VER2-linux-x64.tar.gz \
  && echo '' >> /etc/profile \
  && echo '# JDK' >> /etc/profile \
  && echo "export JAVA_HOME=$JAVA_HOME" >> /etc/profile \
  && echo 'export PATH="$PATH:$JAVA_HOME/bin"' >> /etc/profile \
  && echo '' >> /etc/profile

# Apache Hadoop 
ENV SRC_DIR /opt 
ENV HADOOP_URL http://www.eu.apache.org/dist/hadoop/common 
ENV HADOOP_VERSION hadoop-2.7.1 
RUN cd $SRC_DIR && wget http://parsa.epfl.ch/cloudsuite/software/new_analytic.tar.gz && tar xzf new_analytic.tar.gz && cd new_analytic \
&& tar xzf $HADOOP_VERSION.tar.gz ; rm -f $HADOOP_VERSION.tar.gz
#&& curl -LO "$HADOOP_URL/$HADOOP_VERSION/$HADOOP_VERSION.tar.gz" \ 
#&& tar xzf $HADOOP_VERSION.tar.gz ; rm -f $HADOOP_VERSION.tar.gz 
  
# Hadoop ENV
ENV HADOOP_PREFIX $SRC_DIR/new_analytic/$HADOOP_VERSION 
ENV PATH $PATH:$HADOOP_PREFIX/bin:$HADOOP_PREFIX/sbin 
ENV HADOOP_MAPRED_HOME $HADOOP_PREFIX 
ENV HADOOP_COMMON_HOME $HADOOP_PREFIX 
ENV HADOOP_HDFS_HOME $HADOOP_PREFIX 
ENV YARN_HOME $HADOOP_PREFIX 
RUN echo '# Hadoop' >> /etc/profile \
  && echo "export HADOOP_PREFIX=$HADOOP_PREFIX" >> /etc/profile \
  && echo 'export PATH=$PATH:$HADOOP_PREFIX/bin:$HADOOP_PREFIX/sbin' >> /etc/profile \
  && echo 'export HADOOP_MAPRED_HOME=$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export HADOOP_COMMON_HOME=$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export HADOOP_HDFS_HOME=$HADOOP_PREFIX' >> /etc/profile \
  && echo 'export YARN_HOME=$HADOOP_PREFIX' >> /etc/profile

# Add in the etc/hadoop directory
ADD conf/core-site.xml $HADOOP_PREFIX/etc/hadoop/core-site.xml
ADD conf/hdfs-site.xml $HADOOP_PREFIX/etc/hadoop/hdfs-site.xml
ADD conf/yarn-site.xml $HADOOP_PREFIX/etc/hadoop/yarn-site.xml
ADD conf/mapred-site.xml $HADOOP_PREFIX/etc/hadoop/mapred-site.xml
RUN sed -i '/^export JAVA_HOME/ s:.*:export JAVA_HOME=/usr/local/jdk:' $HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

# Name node foramt
RUN hdfs namenode -format

# SSH conf 
ADD ssh_config /root/.ssh/config
RUN chmod 600 /root/.ssh/config
RUN chown root:root /root/.ssh/config

# Apache Mahout
ENV MAHOUT_URL http://www.eu.apache.org/dist/mahout/
ENV MAHOUT_VERSION 0.11.0
#RUN cd $SRC_DIR && curl -LO "$MAHOUT_URL/$MAHOUT_VERSION/apache-mahout-distribution-$MAHOUT_VERSION-src.tar.gz" \
RUN cd $SRC_DIR/new_analytic && tar xzf apache-mahout-distribution-$MAHOUT_VERSION-src.tar.gz ; rm -f apache-mahout-distribution-$MAHOUT_VERSION-src.tar.gz
 
# MAVEN
ENV MAVEN_URL http://www.eu.apache.org/dist/maven/
ENV MAVEN_VERSION maven-3
ENV MAVEN_VERSION2 3.3.3
#RUN cd $SRC_DIR \ 
#&& curl -LO "$MAVEN_URL/$MAVEN_VERSION/$MAVEN_VERSION2/binaries/apache-maven-$MAVEN_VERSION2-bin.tar.gz" \
RUN cd $SRC_DIR/new_analytic && tar xzf apache-maven-$MAVEN_VERSION2-bin.tar.gz ; rm -f apache-maven-$MAVEN_VERSION2-bin.tar.gz

ENV MAHOUT_HOME $SRC_DIR/new_analytic/apache-mahout-distribution-$MAHOUT_VERSION
ENV MAHOUT_BIN $MAHOUT_HOME/bin
ENV MAVEN_HOME $SRC_DIR/new_analytic/apache-maven-$MAVEN_VERSION2
ENV MAVEN_BIN $MAVEN_HOME/bin
ENV PATH $PATH:$MAVEN_HOME:$MAVEN_BIN:$MAHOUT_HOME:$MAHOUT_BIN


RUN echo '# MAHOUT and MAVEN' >> /etc/profile \
  && echo "export MAHOUT_HOME=$MAHOUT_HOME" >> /etc/profile \
  && echo "export MAVEN_HOME=$MAVEN_HOME" >> /etc/profile \
  && echo 'export PATH=$PATH:$MAHOUT_HOME/bin:$MAVEN_HOME/bin' >> /etc/profile

#install mahout
RUN cd $MAHOUT_HOME && mvn install -DskipTests

#prepare mahout enviorenment 
RUN cd $MAHOUT_HOME/examples && mkdir temp
ADD categories.txt  $MAHOUT_HOME/examples/temp/categories.txt
#RUN cd $MAHOUT_HOME/examples/temp \
#    && wget http://parsa.epfl.ch/cloudsuite/software/enwiki-20100904-pages-articles1.xml.bz2 \
#    && bzip2 -d enwiki-20100904-pages-articles1.xml.bz2 \
#    && wget http://download.wikimedia.org/enwiki/latest/enwiki-latest-pages-articles.xml.bz2 \
#    && bzip2 -d enwiki-latest-pages-articles.xml.bz2
ADD run.sh $MAHOUT_HOME/examples/temp/run.sh

ADD bootstrap.sh /etc/bootstrap.sh
RUN chown root:root /etc/bootstrap.sh
RUN chmod 700 /etc/bootstrap.sh

ENV BOOTSTRAP /etc/bootstrap.sh

# workingaround docker.io build error
RUN ls -la $HADOOP_PREFIX/etc/hadoop/*-env.sh
RUN chmod +x $HADOOP_PREFIX/etc/hadoop/*-env.sh
RUN ls -la $HADOOP_PREFIX/etc/hadoop/*-env.sh

# fix the 254 error code
RUN sed  -i "/^[^#]*UsePAM/ s/.*/#&/"  /etc/ssh/sshd_config
RUN echo "UsePAM no" >> /etc/ssh/sshd_config
RUN echo "Port 2122" >> /etc/ssh/sshd_config

RUN service ssh start && $HADOOP_PREFIX/etc/hadoop/hadoop-env.sh && $HADOOP_PREFIX/sbin/start-dfs.sh && $HADOOP_PREFIX/bin/hdfs dfs -mkdir -p /user/root
RUN service ssh start && $HADOOP_PREFIX/etc/hadoop/hadoop-env.sh && $HADOOP_PREFIX/sbin/start-dfs.sh && $HADOOP_PREFIX/bin/hdfs dfs -put $HADOOP_PREFIX/etc/hadoop/ input

CMD ["/etc/bootstrap.sh", "-d"]

# Hdfs ports
EXPOSE 50010 50020 50070 50075 50090
# Mapred ports
EXPOSE 19888
#Yarn ports
EXPOSE 8030 8031 8032 8033 8040 8042 8088
#Other ports
EXPOSE 49707 2122
