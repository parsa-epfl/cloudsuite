FROM --platform=linux/amd64 cloudsuite/spark:3.3.2 as build

WORKDIR /root
     
# Copy files
COPY benchmark /root/benchmark


ENV SCALA_VERSION 2.13

# Build the benchmark using sbt
RUN set -ex \
    && apt-get update \
    && apt-get install apt-transport-https curl gnupg -y --no-install-recommends \
    && echo "deb https://repo.scala-sbt.org/scalasbt/debian all main" | tee /etc/apt/sources.list.d/sbt.list \
    && echo "deb https://repo.scala-sbt.org/scalasbt/debian /" | tee /etc/apt/sources.list.d/sbt_old.list \
    && curl -sL "https://keyserver.ubuntu.com/pks/lookup?op=get&search=0x2EE0EA64E40A89B84B2DF73499E82A75642AC823" | gpg --no-default-keyring --keyring gnupg-ring:/etc/apt/trusted.gpg.d/scalasbt-release.gpg --import \
    && chmod 644 /etc/apt/trusted.gpg.d/scalasbt-release.gpg \
    && apt-get update -y \
    && apt-get install -y --no-install-recommends sbt \
    && rm -rf /var/lib/apt/lists/*

# build and save `.jar`
RUN cd /root/benchmark \
    && sbt package \
    && mv /root/benchmark/target/scala-$SCALA_VERSION/*.jar /root/benchmark/run_benchmark.sh /root \
    && apt-get purge -y --auto-remove sbt \
    && rm -r /root/benchmark \
    && rm -r /root/.sbt /root/.cache \
    && chmod +x /root/entrypoint.sh

FROM cloudsuite/spark:3.3.2
# copy data from the build container
COPY --from=build /root/graph-analytics-2.0.jar /root
COPY --from=build /root/run_benchmark.sh /root
COPY files/entrypoint.sh /root/entrypoint.sh
RUN chmod +x /root/entrypoint.sh /root/run_benchmark.sh

ENV BENCHMARK_JAR /root/graph-analytics-2.0.jar
# This input file comes from in `datasets/twitter-dataset-graph`
ENV INPUT_FILE /data/edges.csv

ENTRYPOINT ["/root/entrypoint.sh"]
