FROM cloudsuite/java:openjdk11

ENV CASSANDRA_VERSION 4.1.0

ENV CASSANDRA_HOME /opt/cassandra
ENV CASSANDRA_CONFIG /etc/cassandra
ENV PATH $CASSANDRA_HOME/bin:$PATH


COPY install.sh /

RUN ./install.sh



