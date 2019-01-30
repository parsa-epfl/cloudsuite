FROM cloudsuite/java:openjdk8

ENV CASSANDRA_VERSION 3.11.2

ENV YCSB_VERSION 0.14.0

RUN export DEBIAN_FRONTEND=noninteractive\
    && apt-get update \
    && apt-get -y install wget gnupg software-properties-common python \
    &&  wget http://archive.apache.org/dist/cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz \
    && tar -xvzf apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz \
    && groupadd -r cassandra && useradd -r -g cassandra cassandra \ 
    && apt-get autoremove && apt-get clean && apt-get upgrade -y

ENV CASSANDRA_HOME apache-cassandra-$CASSANDRA_VERSION
ENV CASSANDRA_CONFIG $CASSANDRA_HOME/conf
ENV PATH $PATH:$CASSANDRA_HOME/bin

RUN wget -q https://github.com/brianfrankcooper/YCSB/releases/download/$YCSB_VERSION/ycsb-$YCSB_VERSION.tar.gz -O /ycsb-$YCSB_VERSION.tar.gz \
 && tar -xzf /ycsb-$YCSB_VERSION.tar.gz && rm /ycsb-$YCSB_VERSION.tar.gz && mv /ycsb-$YCSB_VERSION /ycsb \
 && chown cassandra:cassandra -R /ycsb/workloads

COPY setup_tables.txt /setup_tables.txt
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

USER cassandra
