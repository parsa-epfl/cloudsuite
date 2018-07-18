FROM ubuntu:14.04

ENV CASSANDRA_VERSION 2.1.12

ENV CASSANDRA_CONFIG /etc/cassandra

RUN apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys 514A2AD631A57A16DD0047EC749D6EEC0353B12C A278B781FE4B2BDA\
	&& echo 'deb http://www.apache.org/dist/cassandra/debian 21x main' >> /etc/apt/sources.list.d/cassandra.list \
	&& apt-get update \
    	&& apt-get install -y cassandra \
    	&& rm -rf /var/lib/apt/lists/*

# https://issues.apache.org/jira/browse/CASSANDRA-11661
RUN sed -ri 's/^(JVM_PATCH_VERSION)=.*/\1=25/' /etc/cassandra/cassandra-env.sh

COPY setup_tables.txt /setup_tables.txt
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
CMD ["cassandra"]
ENTRYPOINT ["/docker-entrypoint.sh"]

EXPOSE 7000 7001 7199 9042 9160
