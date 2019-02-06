FROM cloudsuite/java:openjdk8

ENV CASSANDRA_VERSION 3.11.3

ENV CASSANDRA_CONFIG /etc/cassandra

RUN apt-get update \
	&& apt-get install gnupg tzdata -y --no-install-recommends \
	&& apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys A278B781FE4B2BDA \
	|| apt-key adv --keyserver ipv4.pool.sks-keyservers.net --recv-keys A278B781FE4B2BDA \
	&& echo 'deb http://www.apache.org/dist/cassandra/debian 311x main' >> /etc/apt/sources.list.d/cassandra.list \
	&& apt-get update \
    	&& apt-get install -y cassandra=$CASSANDRA_VERSION \
    	&& rm -rf /var/lib/apt/lists/*

RUN echo "Add limits settings ...\n"\
    && echo "* soft nofile 1000000" >> /etc/security/limits.conf \
    && echo "* hard nofile 1000000" >> /etc/security/limits.conf
