FROM cloudsuite/java:openjdk11

ENV CASSANDRA_VERSION 4.0-alpha3
ENV CASSANDRA_CONFIG /apache-cassandra-${CASSANDRA_VERSION}/conf

RUN apt-get update \
	&& apt-get install -y --no-install-recommends gnupg tzdata wget python curl procps \
	&& wget -O /apache-cassandra-${CASSANDRA_VERSION}-bin.tar.gz https://downloads.apache.org/cassandra/4.0-alpha3/apache-cassandra-${CASSANDRA_VERSION}-bin.tar.gz \
	&& tar xzvf apache-cassandra-${CASSANDRA_VERSION}-bin.tar.gz

RUN echo "Add limits settings ...\n"\
	&& echo "* soft nofile 1000000" >> /etc/security/limits.conf \
	&& echo "* hard nofile 1000000" >> /etc/security/limits.conf
