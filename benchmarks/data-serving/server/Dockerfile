FROM cloudsuite/java:openjdk8

ENV CASSANDRA_VERSION 3.11.2

ENV CASSANDRA_CONFIG /etc/cassandra

RUN export DEBIAN_FRONTEND=noninteractive\
    && apt-get update \
    && apt-get -y install wget \
    &&  wget http://archive.apache.org/dist/cassandra/$CASSANDRA_VERSION/apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz \
    && tar -xvzf apache-cassandra-$CASSANDRA_VERSION-bin.tar.gz

ENV CASSANDRA_HOME apache-cassandra-$CASSANDRA_VERSION
ENV CASSANDRA_CONFIG $CASSANDRA_HOME/conf
ENV PATH $PATH:$CASSANDRA_HOME/bin

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["cassandra", "-R", "-f"]

EXPOSE 7000 7001 7199 9042 9160
