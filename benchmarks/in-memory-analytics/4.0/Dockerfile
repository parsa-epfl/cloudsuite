FROM --platform=linux/amd64 cloudsuite/spark:2.4.5 as build

# Benchmark files
COPY movielens-als /root/movielens-als

WORKDIR /root

# Build the benchmark using sbt
RUN set -ex \
    && apt-get update \
    && apt-get install -y --no-install-recommends gnupg \
    && echo "deb http://dl.bintray.com/sbt/debian /" | tee -a /etc/apt/sources.list.d/sbt.list \
    && apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 642AC823 \
    && apt-get update -y && apt-get install -y --no-install-recommends sbt \
    && rm -rf /var/lib/apt/lists/*

RUN cd /root/movielens-als \
    && sbt package \
    && mkdir -p /benchmarks/movielens-als \
    && mv /root/movielens-als/target/scala-2.11/*.jar /root/movielens-als/run_benchmark.sh /benchmarks/movielens-als \
    && rm -r /root/movielens-als \
    && apt-get purge -y --auto-remove sbt \
    && rm -r /root/.sbt /root/.ivy2

COPY files /root/

FROM cloudsuite/spark:2.4.5
RUN mkdir -p /benchmarks/movielens-als /root
COPY --from=build /benchmarks/movielens-als /benchmarks/movielens-als
COPY --from=build /root/entrypoint.sh /root/entrypoint.sh
ENTRYPOINT ["/root/entrypoint.sh"]
