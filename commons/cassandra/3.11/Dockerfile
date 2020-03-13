FROM cloudsuite/java:openjdk8

ENV CASSANDRA_VERSION 3.11.6
ENV CASSANDRA_CONFIG /etc/cassandra

RUN apt-get update \
	&& apt-get install -y --no-install-recommends gnupg tzdata wget python \
	&& wget -O /tmp/cassandra_${CASSANDRA_VERSION}_all.deb https://archive.apache.org/dist/cassandra/${CASSANDRA_VERSION}/debian/cassandra_${CASSANDRA_VERSION}_all.deb \
	&& dpkg -i /tmp/cassandra_${CASSANDRA_VERSION}_all.deb \
	&& rm -rf /tmp/cassandra_${CASSANDRA_VERSION}_all.deb

RUN echo "Add limits settings ...\n"\
	&& echo "* soft nofile 1000000" >> /etc/security/limits.conf \
	&& echo "* hard nofile 1000000" >> /etc/security/limits.conf
