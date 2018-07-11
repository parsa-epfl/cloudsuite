FROM cloudsuite/java

RUN apt-get update \
 && apt-get install software-properties-common wget -y \
 && buildDeps='python' \
 && set -x \
 && apt-get install -y $buildDeps --no-install-recommends \
 && apt-get autoremove && apt-get clean && apt-get upgrade -y \
 && groupadd -r cassandra && useradd -r -g cassandra cassandra

ENV YCSB_VERSION 0.14.0

RUN wget -q https://github.com/brianfrankcooper/YCSB/releases/download/$YCSB_VERSION/ycsb-$YCSB_VERSION.tar.gz -O /ycsb-$YCSB_VERSION.tar.gz \
 && tar -xzf /ycsb-$YCSB_VERSION.tar.gz && rm /ycsb-$YCSB_VERSION.tar.gz && mv /ycsb-$YCSB_VERSION /ycsb \
 && chown cassandra:cassandra -R /ycsb/workloads

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

USER cassandra
