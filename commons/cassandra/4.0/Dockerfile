FROM cloudsuite/java:openjdk11

ENV CASSANDRA_VERSION 4.0~alpha4
ENV CASSANDRA_CONFIG /etc/cassandra

RUN set -ex \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends gnupg tzdata wget python curl procps \
	&& wget -q --show-progress --progress=bar:force -O /tmp/cassandra_${CASSANDRA_VERSION}_all.deb https://dl.bintray.com/apache/cassandra/pool/main/c/cassandra/cassandra_${CASSANDRA_VERSION}_all.deb \
	&& dpkg --force-depends -i /tmp/cassandra_${CASSANDRA_VERSION}_all.deb \
	&& rm -rf /tmp/cassandra_${CASSANDRA_VERSION}_all.deb \
	&& rm -rf /var/lib/apt/lists/*

RUN echo "Add limits settings ...\n"\
	&& echo "* soft nofile 1000000" >> /etc/security/limits.conf \
	&& echo "* hard nofile 1000000" >> /etc/security/limits.conf
