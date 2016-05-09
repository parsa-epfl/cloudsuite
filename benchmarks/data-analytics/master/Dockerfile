FROM cloudsuite/data-analytics:base

USER root

# Add in the etc/hadoop directory
ADD conf/core-site.xml $HADOOP_PREFIX/etc/hadoop/core-site.xml
ADD conf/hdfs-site.xml $HADOOP_PREFIX/etc/hadoop/hdfs-site.xml
ADD conf/yarn-site.xml $HADOOP_PREFIX/etc/hadoop/yarn-site.xml
ADD conf/mapred-site.xml $HADOOP_PREFIX/etc/hadoop/mapred-site.xml
ADD conf/slaves $HADOOP_PREFIX/etc/hadoop/slaves
RUN sed -i '/^export JAVA_HOME/ s:.*:export JAVA_HOME=/usr/local/jdk:' $HADOOP_PREFIX/etc/hadoop/hadoop-env.sh

ADD start-ssh-serf.sh /root/start-ssh-serf.sh

RUN chmod +x /root/start-ssh-serf.sh

# Name node foramt
RUN hdfs namenode -format

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
ADD run.sh /

EXPOSE 22 7373 7946 9000 50010 50020 50070 50075 50090 50475 8030 8031 8032 8033 8040 8042 8060 8088 50060

CMD '/root/start-ssh-serf.sh'; 'bash'
