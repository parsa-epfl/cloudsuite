FROM cloudsuite/java:openjdk8

ENV YCSB_VERSION 0.14.0

RUN apt-get update \
    && apt-get install gnupg software-properties-common wget curl -y \
    && buildDeps="python cassandra-tools" \
    && set -x \
    && echo "deb https://downloads.apache.org/cassandra/debian 311x main" | tee -a /etc/apt/sources.list.d/cassandra.sources.list \
    && curl https://downloads.apache.org/cassandra/KEYS | apt-key add - \
    && apt-get update \
    && apt-get install -y $buildDeps --no-install-recommends \
    && apt-get autoremove && apt-get clean && apt-get upgrade -y

RUN wget -q https://github.com/brianfrankcooper/YCSB/releases/download/$YCSB_VERSION/ycsb-$YCSB_VERSION.tar.gz -O /ycsb-$YCSB_VERSION.tar.gz \
    && tar -xzf /ycsb-$YCSB_VERSION.tar.gz && rm /ycsb-$YCSB_VERSION.tar.gz && mv /ycsb-$YCSB_VERSION /ycsb \
    && chown cassandra:cassandra -R /ycsb/workloads

COPY setup_tables.txt /setup_tables.txt
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

USER cassandra
