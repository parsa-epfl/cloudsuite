FROM ubuntu:14.04

# Copy files
COPY graph_analytics /root/graph_analytics

WORKDIR /root

# Build the benchmark using sbt
RUN echo "deb http://dl.bintray.com/sbt/debian /" | tee -a /etc/apt/sources.list.d/sbt.list \
    && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 642AC823 \
    && apt-get update -y \
    && apt-get install -y sbt \
    && rm -rf /var/lib/apt/lists/* \
    && sed -i "s/EDGES_FILE/\/data\/edges\.csv/g" graph_analytics/src/main/scala/GraphAnalytics.scala \
    && (cd graph_analytics && sbt package) \
    && mkdir -p /benchmarks/graph_analytics \
    && mv graph_analytics/target/scala-2.10/*.jar graph_analytics/run_benchmark.sh /benchmarks/graph_analytics \
    && rm -r graph_analytics \
    && apt-get purge -y --auto-remove sbt \
    && rm -r .sbt .ivy2

VOLUME ["/benchmarks"]