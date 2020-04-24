FROM cloudsuite/cassandra:4.0

ENV CASSANDRA_VERSION 4.0-alpha3
ENV YCSB_VERSION 0.14.0


RUN apt-get update \
    && apt-get install -y wget \
    &&  wget  -O /tmp/cassandra-tools_${CASSANDRA_VERSION}_all.deb https://downloads.apache.org/cassandra/4.0-alpha3/cassandra-tools_4.0~alpha3_all.deb \
    && dpkg --force-depends -i /tmp/cassandra-tools_${CASSANDRA_VERSION}_all.deb \
    && rm -rf /tmp/cassandra-tools_${CASSANDRA_VERSION}_all.deb

RUN useradd -ms /bin/bash cassandra
RUN wget -q https://github.com/brianfrankcooper/YCSB/releases/download/$YCSB_VERSION/ycsb-$YCSB_VERSION.tar.gz -O /ycsb-$YCSB_VERSION.tar.gz \
    && tar -xzf /ycsb-$YCSB_VERSION.tar.gz && rm /ycsb-$YCSB_VERSION.tar.gz && mv /ycsb-$YCSB_VERSION /ycsb \
    && chown cassandra:cassandra -R /ycsb/workloads
COPY setup_tables.txt /setup_tables.txt
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

USER cassandra
