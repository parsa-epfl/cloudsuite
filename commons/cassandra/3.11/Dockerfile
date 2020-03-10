FROM cloudsuite/java:openjdk8

ENV CASSANDRA_CONFIG /etc/cassandra

RUN apt-get update \
	&& apt-get install gnupg tzdata curl -y --no-install-recommends \
	&& echo "deb https://downloads.apache.org/cassandra/debian 311x main" | tee -a /etc/apt/sources.list.d/cassandra.sources.list \
	&& curl https://downloads.apache.org/cassandra/KEYS | apt-key add - \
	&& apt-get update \
	&& apt-get install -y cassandra \
	&& rm -rf /var/lib/apt/lists/*

RUN echo "Add limits settings ...\n"\
	&& echo "* soft nofile 1000000" >> /etc/security/limits.conf \
	&& echo "* hard nofile 1000000" >> /etc/security/limits.conf
