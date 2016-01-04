FROM ubuntu

RUN apt-key adv --keyserver ha.pool.sks-keyservers.net --recv-keys 514A2AD631A57A16DD0047EC749D6EEC0353B12C

RUN echo 'deb http://www.apache.org/dist/cassandra/debian 21x main' >> /etc/apt/sources.list.d/cassandra.list

ENV CASSANDRA_VERSION 2.1.10

RUN apt-get update \
	&& apt-get install -y cassandra \
	&& rm -rf /var/lib/apt/lists/*

ENV CASSANDRA_CONFIG /etc/cassandra

RUN sed -ri ' s/^(rpc_address:).*/\1 0.0.0.0/; \
	' "$CASSANDRA_CONFIG/cassandra.yaml" && \
    sed -ri ' s/^(# broadcast_rpc_address:).*/\1 1.2.3.4/; \
	' "$CASSANDRA_CONFIG/cassandra.yaml" && \
    sed -i 's/# broadcast_rpc_address:/broadcast_rpc_address:/g' \
        "$CASSANDRA_CONFIG/cassandra.yaml"

USER cassandra

EXPOSE 7000 7001 7199 9042 9160
